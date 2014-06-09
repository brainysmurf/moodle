<?php

namespace \ClassroomTechTools\AppTemplate;

class OutputManager
{
	private $app;

	/**
	 * Array of which tabs are shown in differnet modes
	 */
	public $tabs = array(
		'student' => array(
			'index' => array('index.php', '<i class="icon-calendar"></i> To Do'),
			'classes' => array('classes.php', '<i class="icon-group"></i> View by Class'),
		),
		'student' => array(
			'index' => array('index.php', '<i class="icon-calendar"></i> To Do'),
			'classes' => array('classes.php', '<i class="icon-group"></i> View by Class'),
		),
		'teacher' => array(
			'index' => array('index.php', '<i class="icon-check"></i> Manage Submissions'),
			'classes' => array('classes.php', '<i class="icon-group"></i> View by Class'),
			'teacherstuff' => array('history.php', '<i class="icon-th-list"></i> View History'),
		),
	);

	/**
	 * Array of infomation to show on the tabs for each mode
	 */
	public $modeTabs = array(
		'admin' => '<i class="icon-wrench"></i> Admin Mode',
		'student' => '<i class="icon-user"></i> Student Mode',
		'teacher' => '<i class="icon-magic"></i> Teacher Mode',
	);

	public function __construct(App $app)
	{
		$this->app = $app;
	}

	/**
	 * Display the large tabs to switch between different views (pages) in a mode
	 */
	public function tabs($currentVew)
	{
		$tabs = $this->tabs[$mode];

		$t = '';

		// Add the mode tabs at the top
		$t .= $this->modeTabs();

		$t .= '<div class="tabs"><ul>';

		foreach ($tabs as $name => $tab) {
			$t .= '<li>';
			$t .= '<a ' . ($name == $current ? 'class="selected"': '') . 'href="' . $this->app->getPath() . $tab[0] . '">' . $tab[1] . '</a>';
			$t .= '</li>';
		}

		$t .= '</ul></div>';

		return $t;
	}

	/**
	 * Display the smaller tabs at the top for the different modes a user can switch between
	 */
	private function modeTabs()
	{
		$possibleModes = $this->app->getPossibleModes();
		$currentMode = $this->app->getCurrentMode();

		if (count($possibleModes) < 2) {
			return false;
		}

		$t = '<div class="tabs noborder"><ul class="additional-tabs">';

		foreach ($possibleModes as $mode) {
			$t .= '<li>';
			$t .= '<a ' . ($mode == $currentMode ? 'class="selected"': '') . 'href="' . $this->app->getPath() . 'changemode.php?mode=' . $mode . '">' . $this->modeTabs[$mode] . '</a>';
			$t .= '</li>';
		}

		$t .= '</ul></div>';

		return $t;
	}
}
