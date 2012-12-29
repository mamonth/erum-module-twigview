<?php

/**
 * Twig template engine wrapper module
 * 
 * @author Andrew Tereshko <andrew.tereshko@gmail.com>
 */
class TwigView extends \Erum\ModuleAbstract
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
        require_once dirname( __DIR__ ) . '/external/Twig/lib/Twig/Autoloader.php';
        Twig_Autoloader::register();

        $appCfg = Erum::instance()->config();
        
        $this->loader = new Twig_Loader_Filesystem( $appCfg->application->root . '/templates' );

        $tmpDir = $appCfg->application->root . '/tmp';

        if( !file_exists( $tmpDir ) )
        {
            mkdir( $tmpDir );
        }
        
        $this->environment = new Twig_Environment( $this->loader, array(
            'cache' => $tmpDir,
            'debug' => $appCfg->application->debug,
            'strict_variables' => $appCfg->application->debug ? true : false,
            'optimizations' => -1,
        ) );
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
}
