<?php

/**
 * Class to fetch data from Destiny database
 */

use \PDO;

class Destiny
{
	private $config;
	private $db = null;

	function __construct()
	{
		$this->config = require __DIR__ . '/config.php';
		$this->connectToDb();
	}

	/**
	 * Connect to database and return a PDO object
	 */
	private function connectToDb()
	{
		$this->db = new PDO(
			"dblib:host={$this->config['DB_HOST']};dbname={$this->config['DB_NAME']}",
			$this->config['DB_USER'],
			$this->config['DB_PASS']
		);
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
	}

	/**
	 * Performs a SELECT query and returns an array of the result objects
	 */
	private function select($sql, $params = array())
	{
		$q = $this->db->prepare($sql);
		$q->execute($params);
		return $q->fetchAll();
	}

	/**
	 * Returns the SQL select statement used to get a user's info from Destiny
	 */
	private function getSQL()
	{
		return "SELECT
			pat.FirstName + ' ' + pat.LastName AS 'patron_name',
			pat.DistrictID AS 'patron_districtid',
			sitepat.PatronBarcode AS 'patron_barcode',
			cpy.DateDue AS 'due',
			cpy.CallNumber AS 'call_number',
			bibmstr.Title AS title
		FROM
			CircCatAdmin.Copy cpy
		JOIN
			CircCatAdmin.Patron pat ON pat.PatronID = cpy.PatronID
		JOIN
			CircCatAdmin.SitePatron sitepat ON sitepat.PatronID = cpy.PatronID
		LEFT JOIN
			CircCatAdmin.BibMaster bibmstr ON bibmstr.BibID = cpy.BibID
		WHERE
			cpy.dateOut IS NOT NULL
			AND
			cpy.dateReturned IS NULL ";
	}

	public function getUsersCheckedOutBooks($userDistrictID)
	{
		$sql = $this->getSQL();
		$sql .= 'AND pat.DistrictID = ?';
		return $this->select($sql, array($userDistrictID));
	}

	public function getFamilyCheckedOutBooks($familyID)
	{
		$sql = $this->getSQL();
		$sql .= "AND pat.DistrictID LIKE ?";
		$familyID .= '%';
		return $this->select($sql, array($familyID));
	}

	public function dumpCheckedOutBooks()
	{
		// Original SQL
		/*$select = "
			cpy.CopyID,
			cpy.BibID,
			cpy.CopyBarcode,
			cpy.CallNumber,
			bibmstr.Title,
			cpy.DateOut,
			cpy.DateDue,
			cpy.DateReturned,
			pat.PatronID,
			pat.FirstName,
			pat.LastName,
			pat.UserID,
			pat.DistrictID,
			pat.EmailAddress1
		";*/

		// Tweaked to return the columns with the names neeed to store in the Moodle DB
		$select = "
			pat.FirstName + ' ' + pat.LastName AS 'patron_name',
			pat.DistrictID AS 'patron_districtid',
			sitepat.PatronBarcode AS 'patron_barcode',
			cpy.DateDue AS 'due',
			cpy.CallNumber AS 'call_number',
			bibmstr.Title AS title
		";

		$sql = "SELECT
			{$select}
		FROM
			CircCatAdmin.Copy cpy
		JOIN
			CircCatAdmin.Patron pat ON pat.PatronID = cpy.PatronID
		JOIN
			CircCatAdmin.SitePatron sitepat ON sitepat.PatronID = cpy.PatronID
		LEFT JOIN
			CircCatAdmin.BibMaster bibmstr ON bibmstr.BibID = cpy.BibID
		WHERE
			cpy.dateOut IS NOT NULL
			AND
			cpy.dateReturned IS NULL";

		return $this->select($sql);
	}
}
