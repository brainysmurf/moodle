<?php

	/**
	 * Class ssismetadata
	 * For getting / setting custom fields for courses and categories
	 * (Called SSIS metadata so that there aren't name clashes if moodle adds metadata tables / function)
	 * This requires you have created the _course_ssis_metadata and _category_ssis_metadata tables
	 * See: ssismetadatatables.sql to do that
	 *
	 * To define what custom fields can be set by users...
	 * For courses, edit /course/edit_form.php
	 * For categories , edit /course/editcategory_form.php
	 *
	 * @version 2013-11-11 09:14
	 * @author Anthony Kuske www.anthonykuske.com
	 */
	class ssismetadata
	{
		private $cache;
	
		function __construct()
		{
			// Create cache instance
			$this->cache = cache::make_from_params(cache_store::MODE_APPLICATION, 'ssismetadata', 'ssismetadata');
		}



		// !Get course fields

		/**
		 * Returns an associative array of the custom data stored for a course
		 * @param $courseid
		 * @return array array( 'field' => 'value' );
		 */
		function getCourseFields( $courseid )
		{
			return $this->getAllFields( 'course' , $courseid );
		}

		/**
		 * Returns the value for a single field for a course
		 * @param $courseid
		 * @param $field
		 * @return string|null
		 */
		function getCourseField( $courseid , $field )
		{
			$data = $this->getAllFields( 'course' , $courseid );
			return $data[ $field ];
		}



		// !Get category fields

		/**
		 * Returns an associative array of the custom data stored for a course category
		 * @param $categoryid
		 * @return array array( 'field' => 'value' );
		 */
		function getCategoryFields( $categoryid )
		{
			return $this->getAllFields( 'category' , $categoryid );
		}

		/**
		 * Returns the value for a single field for a course category
		 * @param $categoryid
		 * @param $field
		 * @return string|null
		 */
		function getCategoryField( $categoryid , $field )
		{
			$data = $this->getAllFields( 'course' , $categoryid );
			return $data[ $field ];
		}

		

		/**
		 * Returns all the data for a course or category
		 * Used by the getAllCourseData, getCourseField, getCategoryField
		 * @param $what course or category
		 * @param $id courseid or categoryid
		 * @return array|false
		 */
		private function getAllFields( $what , $id )
		{
			$cacheKey = 'metadata'.$what.$id;

			//Return a cached copy if it exists
			if ( $data = $this->cache->get($cacheKey) )
			{
				return $data;
			}
			
			//Get rows from database
			global $DB;
			
			//Get name of the table
			$table = $this->getTableName( $what );
						
			$rows = $DB->get_records( $table['table'] , array($table['id']=>$id) , '', 'field,value' );
			$data = $this->rowsToArray( $rows );
			
			//Cache it
			$this->cache->set($cacheKey,$data);
			
			return $data;
		}
		


		// !Set course fields

		/**
		 * Set the value for a field for a course
		 * @param $courseid
		 * @param $field
		 * @param $value
		 * @return bool
		 */
		function setCourseField( $courseid , $field , $value )
		{
			return $this->setField( 'course' , $courseid , $field , $value );
		}
		
		// !Set category fields

		/**
		 * Set the value for a field for a category
		 * @param $categoryid
		 * @param $field
		 * @param $value
		 * @return bool
		 */
		function setCategoryField( $categoryid , $field , $value )
		{
			return $this->setField( 'category' , $categoryid , $field , $value );
		}
		
		
		/**
		* Store custom data for a course
		* WIll update the row in the database if it exists, or create a new row otherwise
		* @param $what course or category
		* @param $id courseid or categoryid
		* @param $field name of the field to set
		* @param $value value for the new field
		* @return bool true on success
		 * @throws coding_exception
		*/
		private function setField( $what , $id , $field , $value )
		{
			if ( !$field )
			{
				throw new coding_exception('Trying to setCourseData without a field name.');
			}
			
			global $DB;
			
			//Get the name of the table to update
			$table = $this->getTableName( $what );

			//Was the field already set
			$oldData = $this->getAllFields( $what , $id );
			if ( isset($oldData[$field]) )
			{
				if ( $oldData[$field] === $value )
				{
					//Don't bother updating if the new value is the same
					return true;
				}

				//UPDATE
				$success = $DB->set_field( $table['table'] , 'value' , $value , array($table['id']=>$id, 'field'=>$field) );
			}
			else
			{
				//Create the database row
				$row = new stdClass();
				$row->{$table['id']} = $id;
				$row->field = $field;
				$row->value = $value;

				//INSERT
				$success = $DB->insert_record( $table['table'] , $row , false );
			}
			
			if ( $success )
			{
				//Clear cache
				$cacheKey = 'metadata'.$what.$id;
				$this->cache->delete($cacheKey);
			}
			
			return $success;
		}
		
		
		
		/**
		* Takes an array of rows from the database and returns the field=>value associative array
		* @param $rows An array of records from the Moodle DB get_records
		* @return array
		*/
		private function rowsToArray( $rows )
		{
			$data = array();
			foreach ( $rows as $row )
			{
				$data[ $row->field ] = $row->value;
			}
			return $data;
		}
		
		/**
		* Returns the table name (without the mdl prefix) to store data in, depending on if we're setting fields for a course or category
		* @param $what course or category
		* @return array array('table'=>'name of the table', 'id' => 'Name of the ID column - courseid or categoryid' );
		* @throws coding_exception
		*/
		private function getTableName( $what )
		{
			switch( $what )
			{
				case 'course':
					return array(
						'table'=>'course_ssis_metadata',
						'id'=>'courseid'
					);
				break;
				
				case 'category':
					return array(
						'table'=>'category_ssis_metadata',
						'id'=>'categoryid'
					);
				break;
				
				default:
					throw new coding_exception("Unknown item type ($what).");
				break;
			}
		}
	
	}

?>