<?php

use App\Models\Extensions\BaseConfigMigration;

return new class() extends BaseConfigMigration {
	public function getConfigs(): array
	{
		return [
			[
				'key' => 'is_trial',
				'value' => '0',
				'cat' => 'lychee SE',
				'type_range' => self::BOOL,
				'description' => 'Enable trial mode',
				'details' => 'Enable trial mode for Lychee SE features.',
				'is_secret' => false,
				'level' => 1,
			],
		];
	}
};
