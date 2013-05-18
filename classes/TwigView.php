<?php

/**
 * Twig template engine wrapper module
 *
 * @author Andrew Tereshko <andrew.tereshko@gmail.com>
 */
class TwigView extends \Erum\ModuleAbstract implements \Erum\ViewInterface
{
    /**
     *
     * @var array
     */
    protected $variables = array();

    /**
     *
     * @var Twig_Environment
     */
    protected $environment;

    /**
     *
     * @var Twig_TemplateInterface
     */
    protected $template;

    /**
     * Module bootstrap method
     */
    public static function init()
    {
        require_once dirname( __DIR__ ) . '/external/Twig/lib/Twig/Autoloader.php';
        Twig_Autoloader::register();
    }

    /**
     *
     * @param type $configAlias
     * @return \TwigView
     */
    public static function factory( $configAlias = 'default' )
    {
        return new self( Erum\ModuleDirector::getModuleConfig( parent::getAlias(), $configAlias )  );
    }

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct( array $config )
    {
        $appCfg = Erum::instance()->config();

        $this->loader = new Twig_Loader_Filesystem( $config['templateDirectory'] );

        if( !file_exists( $config['tempDirectory'] ) )
        {
            mkdir( $config['tempDirectory'] );
        }

        $this->environment = new Twig_Environment( $this->loader, array(
            'cache' => $config['tempDirectory'],
            'debug' => $appCfg->application->debug,
           // 'strict_variables' => $appCfg->application->debug ? true : false,
            'optimizations' => -1,
        ) );

        // Add HMVC requests support
        $this->environment->addFunction('subRequest', new Twig_Function_Function( 'subRequest', array( 'is_safe' => array('html') ) ) );

        // various filters
        $this->environment->addFilter( 'gmdate', new Twig_SimpleFilter('gmdate', 'gmdate') );

    }

    public function setTemplate( $templateName )
    {
        $this->template = $this->environment->loadTemplate( $templateName );

        return $this;
    }

    /**
     *
     * @return Twig_TemplateInterface
     */
    public function getTemplate()
    {
        return $this->template;
    }

    public function setVar( $variable, $value )
    {
        $this->variables[ $variable ] = $value;

        return $this;
    }

    public function setGlobal( $variable, $value )
    {
        $this->environment->addGlobal( $variable, $value );

        return $this;
    }

    public function display()
    {
        echo $this->fetch();
    }

    public function fetch()
    {
        return $this->template->render( $this->variables );
    }

    public function getContentType()
    {
        return 'text/html';
    }
}

function subRequest( $uri, $method = \Erum\Request::GET )
{
    return \Erum\Request::factory( $uri, $method )->execute()->body;
}
