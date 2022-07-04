<?php

namespace MinecraftServerChecker;

use XF\AddOn\AbstractSetup;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
	public function install(array $stepParams = [])
	{
		$em = \XF::app()->em();

		$this->schemaManager()->createTable('xf_msc_servers', function(Create $table) {
			$table->addColumn('thread_id', 'int');
			$table->addColumn('ip', 'varchar', 100);
			$table->addColumn('status', 'tinyint')->setDefault(0);
			$table->addColumn('online', 'int')->setDefault(0);
			$table->addColumn('max', 'int')->setDefault(1);
			$table->addColumn('last_update', 'varchar', 100);
			$table->addPrimaryKey('thread_id');
		});

		if (!$em->find('XF:ThreadField', 'msc_server_ip')) {
			$field = $em->create('XF:ThreadField');

			$title = $field->getMasterPhrase(true);
			$title->phrase_text = "Minecraft Server IP Address";

			$description = $field->getMasterPhrase(false);
			$description->phrase_text = "Enter the valid ip address for the server connection.";

			$field->set('field_id', 'msc_server_ip');
			$field->set('match_type', 'regex');
			$field->setFromEncoded('match_params', '{"regex":"(([a-zA-Z0-9-])+[.]+([a-zA-Z0-9-])+[.]+([a-zA-Z0-9-])+)|([a-zA-Z0-9-])+[.]+([a-zA-Z0-9-])+"}');

			$field->addCascadedSave($title);
			$field->addCascadedSave($description);
			$field->save();
		}
	}

	public function upgrade(array $stepParams = [])
	{
		// First Version
	}

	public function uninstall(array $stepParams = [])
	{
		$em = \XF::app()->em();

		$field = $em->find('XF:ThreadField', 'msc_server_ip');
		if ($field) $field->delete();

		$this->schemaManager()->dropTable('xf_msc_servers');
		
	}
}