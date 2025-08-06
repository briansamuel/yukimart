<?php
namespace App\Exceptions;

use Illuminate\Support\Facades\Log;

class CheckException extends \Exception
{
	protected $code;
	protected $content;

	public function __construct($message, $code, $content = [])
	{
		$this->code = $code;
		$this->content = $content;

		// log exception
		Log::warning($message, $content);

		parent::__construct($message);
	}

	public function getErrorCode()
	{
		return $this->code;
	}

	public function getContent()
	{
		return $this->content;
	}
}
