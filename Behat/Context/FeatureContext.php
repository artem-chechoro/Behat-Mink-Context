# features/bootstrap/FeatureContext.php
<?php

use Behat\Behat\Event\StepEvent;
use Behat\Mink\Exception\ResponseTextException;
use Behat\MinkExtension\Context\MinkContext;
use Sanpi\Behatch\Context\BrowserContext;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use PHPUnit_Framework_Assert as Assert;

class FeatureContext extends BehatContext
{
    /**
     * @var Kernel
     */
    protected $kernel;

    private $parameters;

    /**
     * @var bool
     */
    private $enableAjaxWait = true;

    /**
     * Initializes context with parameters from behat.yml.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;

        $this->useContext('mink-extra', new \Weavora\MinkExtra\Context\MinkExtraContext());
        $this->useContext('tests-bundle', new RootContext());

    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario()
    {

    }
    /**
     * @BeforeStep @javascript
     */
    public function beforeStep($event)
      {
        $text = $event->getStep()->getText();
      if (preg_match('/(follow|press|click|check|submit|select|go|track|should|am)/i', $text)) {
        $this->ajaxClickHandlerBefore();
    }
    }

    /**
     * @AfterStep @javascript
     */
    public function afterStep($event)
    {
        $text = $event->getStep()->getText();
        if (preg_match('/(follow|press|click|check|submit|select|go|track|should|am)/i', $text)) {
            $this->ajaxClickHandlerAfter();
        }
    }

    /**
     * @AfterStep @javascript
     */
    public function makeScreenShot(StepEvent $event)
    {

        $step = $event->getStep();
        $path = array(
            'date' => date("Ymd-Hi"),
            'feature' => $step->getParent()->getFeature()->getTitle(),
            'scenario' => $step->getParent()->getTitle(),
            'step' => $step->getType() . ' ' . $step->getText()
        );
        $path = preg_replace('/[^\-\.\w]/', '_', $path);
        $filename = './build/screenshots/' . implode('/', $path) . '.jpg';
        // Create directories if needed
        if (!@is_dir(dirname($filename))) {
            @mkdir(dirname($filename), 0775, true);
        }
        if ($event->getResult() == StepEvent::FAILED) {
            file_put_contents($filename, $this->getMink()->getSession()->getScreenshot());
        }
    }

    /**
     * @Given /^I reset session$/
     */
    public function iResetTheSession()
    {
        $this->getSession()->restart();
    }

    /**
     * @Then /^I click on "(?P<selector>.*?)" element$/
     */
    public function click($selector)
    {
        $element = $this->getSession()->getPage()->find('css', $selector);
        Assert::assertNotEmpty($element, "Can't find element via css selector `{$selector}`");
        $this->ajaxClickHandlerBefore();
        $element->click();
        $this->ajaxClickHandlerAfter();
    }

}