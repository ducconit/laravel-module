<?php

namespace DNT\Module\Enums;

enum ModuleStatus: string
{
	case ENABLE = 'enable';
	case DISABLE = 'disable';

	public static function toString(?bool $status): string
	{
		return $status ? self::ENABLE->value : self::DISABLE->value;
	}

	public static function isEnable(?string $status): bool
	{
		return $status == self::ENABLE->value || $status;
	}
}
