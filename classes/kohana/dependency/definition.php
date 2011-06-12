<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Dependency_Definition {

	protected static $_definitions = array();
	protected static $_defaults = array
	(
		'class'       => NULL,    // The class that is to be created.
		'path'        => NULL,    // The path to the file containing the class. Will try to autoload the class if no path is provided. Assumes ".php" extension.
		'constructor' => NULL,    // The method used to create the class. Will use "__construct()" if none is provided.
		'arguments'   => NULL,    // The arguments to be passed to the constructor method.
		'shared'      => FALSE,   // The shared setting determines if the object will be cached.
		'methods'     => array(), // Additional methods (and their arguments) that need to be called on the created object.
	);
	
	protected $_settings;
	
	public function __construct($key, Config $config)
	{
		// Store the list of definitions from the config
		if (empty(self::$_definitions))
		{
			self::$_definitions = $config->load('dependencies')->as_array();
		}

		// Merge all relevant dependency definitions into one collection of settings
		$settings = self::$_defaults;
		$current_path = '';
		foreach (explode('.', $key) as $sub_key)
		{
			$current_path = trim($current_path.'.'.$sub_key, '.');
			$path_settings = Arr::path(self::$_definitions, $current_path.'._settings', array());
			$settings = Arr::overwrite($settings, $path_settings);
		}
		
		// Make sure the "class" setting is valid
		if (empty($settings['class']))
		{
			$settings['class'] = str_replace(' ', '_', ucwords(str_replace('_', ' ', $settings['class'])));
		}
		
		// Make sure the "path" setting is valid
		if ( ! is_string($settings['path']))
		{
			$settings['path'] = NULL;
		}
		
		// Make sure the "constructor" setting is valid
		if ( ! is_string($settings['constructor']))
		{
			$settings['constructor'] = NULL;
		}
		
		// Make sure the "arguments" setting is valid
		if ( ! is_array($settings['arguments']))
		{
			$settings['arguments'] = array();
		}
		
		// Make sure the "shared" setting is valid
		$settings['shared'] = (bool) $settings['shared'];
		
		// Make sure the "methods" setting is valid
		if (is_array($settings['methods']))
		{
			$methods = array();
			foreach ($settings['methods'] as $method)
			{
				$method_name = (isset($method[0]) AND is_string($method[0])) ? $method[0] : NULL;
				$arguments   = (isset($method[1]) AND is_array($method[1])) ? $method[1] : NULL;
				$methods[]   = array($method_name, $arguments);
			}
			
			$settings['methods'] = $methods;
		}
		else
		{
			$settings['methods'] = array();
		}

		$this->_settings = $settings;
	}

	public function __get($setting)
	{
		if (array_key_exists($setting, $this->_settings))
			return $this->_settings[$setting];
		else
			return NULL;
	}
	
	public function __isset($setting)
	{
		return (bool) array_key_exists($setting, $this->_settings);
	}
}
