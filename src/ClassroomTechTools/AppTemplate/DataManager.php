<?php

namespace \ClassroomTechTools\AppTemplate;

class DataManager
{
	private $app;

	public function __construct(App $app)
	{
		$this->app = $app;
	}
}
