<?php
/**
 * 2013-2014 Frédéric BENOIST
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 *  @author    Frédéric BENOIST <fbenoist@nextimt.com>
 *  @copyright 2013-2014 Frédéric BENOIST
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_'))
	exit;

class BoLogActivity extends Module
{
	public function __construct()
	{
		$this->name = 'bologactivity';
		$this->tab = 'administration';
		$this->version = '1.1';
		$this->author = 'Frédéric BENOIST';
		$this->need_instance = 0;
		$this->is_configurable = 0;
		$this->bootstrap = true;

		parent::__construct();
		$this->displayName = $this->l('BO Log Activity');
		$this->description = $this->l('Log module install/uninstall and carrier update');
	}

	public function install()
	{
		if (!parent::install()
			|| !$this->registerHook('actionCarrierUpdate')
			|| !$this->registerHook('actionModuleInstallAfter')
			|| !$this->registerHook('actionModuleRegisterHookAfter')
			|| !$this->registerHook('actionModuleUnRegisterHookAfter'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!$this->unregisterHook('actionModuleInstallAfter')
			|| !$this->unregisterHook('actionModuleRegisterHookAfter')
			|| !$this->unregisterHook('actionModuleUnRegisterHookAfter')
			|| !parent::uninstall())
			return false;
		return true;
	}

	private static function logObjectEvent($event, $object)
	{
		if ((!Validate::isLoadedObject(Context::getContext()->employee)) || (!Validate::isLoadedObject($object)))
			return;

		if (get_class($object) == get_class())
			return;

		if ((!Context::getContext()->employee->isLoggedBack()))
			return;

		$log_message = sprintf('(%s) %s', get_class($object), $event );
		PrestaShopLogger::addLog($log_message, 1, null, get_class($object), (int)$object->id, true, (int)Context::getContext()->employee->id);

	}

	public function hookactionCarrierUpdate($params)
	{
		self::logObjectEvent('Update parameters', $params['carrier']);
	}

	public function hookactionModuleInstallAfter($params)
	{
		self::logObjectEvent('Install module', $params['object']);
	}

	public function hookactionModuleRegisterHookAfter($params)
	{
		if (!Validate::isHookName($params['hook_name']))
			return;
		self::logObjectEvent('Register Hook '.$params['hook_name'], $params['object']);
	}

	public function hookactionModuleUnRegisterHookAfter($params)
	{
		if (!Validate::isHookName($params['hook_name']))
			return;
		self::logObjectEvent('Unregister Hook '.$params['hook_name'], $params['object']);
	}
}