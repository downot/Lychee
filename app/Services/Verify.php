<?php

namespace App\Services;

use App\Models\Configs;
use LycheeVerify\Verify as BaseVerify;

class Verify extends BaseVerify
{
	public function is_trial(): bool
	{
		return Configs::getValueAsBool('is_trial');
	}
}
