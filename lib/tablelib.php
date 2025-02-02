<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    core
 * @subpackage lib
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**#@+
 * These constants relate to the table's handling of URL parameters.
 */
define('TABLE_VAR_SORT',   1);
define('TABLE_VAR_HIDE',   2);
define('TABLE_VAR_SHOW',   3);
define('TABLE_VAR_IFIRST', 4);
define('TABLE_VAR_ILAST',  5);
define('TABLE_VAR_PAGE',   6);
/**#@-*/

/**#@+
 * Constants that indicate whether the paging bar for the table
 * appears above or below the table.
 */
define('TABLE_P_TOP',    1);
define('TABLE_P_BOTTOM', 2);
/**#@-*/


/**
 * @package   moodlecore
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class flexible_table {

    var $uniqueid        = NULL;
    var $attributes      = array();
    var $headers         = array();
    var $columns         = array();
    var $column_style    = array();
    var $column_class    = array();
    var $column_suppress = array();
    var $column_nosort   = array('userpic');
    private $column_textsort = array();
    var $setup           = false;
    var $sess            = NULL;
    var $baseurl         = NULL;
    var $request         = array();

    var $is_collapsible = false;
    var $is_sortable    = false;
    var $use_pages      = false;
    var $use_initials   = false;

    var $maxsortkeys = 2;
    var $pagesize    = 30;
    var $currpage    = 0;
    var $totalrows   = 0;
    var $currentrow  = 0;
    var $sort_default_column = NULL;
    var $sort_default_order  = SORT_ASC;

    /**
     * Array of positions in which to display download controls.
     */
    var $showdownloadbuttonsat= array(TABLE_P_TOP);

    /**
     * @var string Key of field returned by db query that is the id field of the
     * user table or equivalent.
     */
    public $useridfield = 'id';

    /**
     * @var string which download plugin to use. Default '' means none - print
     * html table with paging. Property set by is_downloading which typically
     * passes in cleaned data from $
     */
    var $download  = '';

    /**
     * @var bool whether data is downloadable from table. Determines whether
     * to display download buttons. Set by method downloadable().
     */
    var $downloadable = false;

    /**
     * @var string which download plugin to use. Default '' means none - print
     * html table with paging.
     */
    var $defaultdownloadformat  = 'csv';

    /**
     * @var bool Has start output been called yet?
     */
    var $started_output = false;

    var $exportclass = null;

    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    function __construct($uniqueid) {
        $this->uniqueid = $uniqueid;
        $this->request  = array(
            TABLE_VAR_SORT   => 'tsort',
            TABLE_VAR_HIDE   => 'thide',
            TABLE_VAR_SHOW   => 'tshow',
            TABLE_VAR_IFIRST => 'tifirst',
            TABLE_VAR_ILAST  => 'tilast',
            TABLE_VAR_PAGE   => 'page',
        );
    }

    /**
     * Call this to pass the download type. Use :
     *         $download = optional_param('download', '', PARAM_ALPHA);
     * To get the download type. We assume that if you call this function with
     * params that this table's data is downloadable, so we call is_downloadable
     * for you (even if the param is '', which means no download this time.
     * Also you can call this method with no params to get the current set
     * download type.
     * @param string $download download type. One of csv, tsv, xhtml, ods, etc
     * @param string $filename filename for downloads without file extension.
     * @param string $sheettitle title for downloaded data.
     * @return string download type.  One of csv, tsv, xhtml, ods, etc
     */
    function is_downloading($download = null, $filename='', $sheettitle='') {
        if ($download!==null) {
            $this->sheettitle = $sheettitle;
            $this->is_downloadable(true);
            $this->download = $download;
            $this->filename = clean_filename($filename);
            $this->export_class_instance();
        }
        return $this->download;
    }

    /**
     * Get, and optionally set, the export class.
     * @param $exportclass (optional) if passed, set the table to use this export class.
     * @return table_default_export_format_parent the export class in use (after any set).
     */
    function export_class_instance($exportclass = null) {
        if (!is_null($exportclass)) {
            $this->started_output = true;
            $this->exportclass = $exportclass;
            $this->exportclass->table = $this;
        } else if (is_null($this->exportclass) && !empty($this->download)) {
            $classname = 'table_'.$this->download.'_export_format';
            $this->exportclass = new $classname($this);
            if (!$this->exportclass->document_started()) {
                $this->exportclass->start_document($this->filename);
            }
        }
        return $this->exportclass;
    }

    /**
     * Probably don't need to call this directly. Calling is_downloading with a
     * param automatically sets table as downloadable.
     *
     * @param bool $downloadable optional param to set whether data from
     * table is downloadable. If ommitted this function can be used to get
     * current state of table.
     * @return bool whether table data is set to be downloadable.
     */
    function is_downloadable($downloadable = null) {
        if ($downloadable !== null) {
            $this->downloadable = $downloadable;
        }
        return $this->downloadable;
    }

    /**
     * Where to show download buttons.
     * @param array $showat array of postions in which to show download buttons.
     * Containing TABLE_P_TOP and/or TABLE_P_BOTTOM
     */
    function show_download_buttons_at($showat) {
        $this->showdownloadbuttonsat = $showat;
    }

    /**
     * Sets the is_sortable variable to the given boolean, sort_default_column to
     * the given string, and the sort_default_order to the given integer.
     * @param bool $bool
     * @param string $defaultcolumn
     * @param int $defaultorder
     * @return void
     */
    function sortable($bool, $defaultcolumn = NULL, $defaultorder = SORT_ASC) {
        $this->is_sortable = $bool;
        $this->sort_default_column = $defaultcolumn;
        $this->sort_default_order  = $defaultorder;
    }

    /**
     * Use text sorting functions for this column (required for text columns with Oracle).
     * Be warned that you cannot use this with column aliases. You can only do this
     * with real columns. See MDL-40481 for an example.
     * @param string column name
     */
    function text_sorting($column) {
        $this->column_textsort[] = $column;
    }

    /**
     * Do not sort using this column
     * @param string column name
     */
    function no_sorting($column) {
        $this->column_nosort[] = $column;
    }

    /**
     * Is the column sortable?
     * @param string column name, null means table
     * @return bool
     */
    function is_sortable($column = null) {
        if (empty($column)) {
            return $this->is_sortable;
        }
        if (!$this->is_sortable) {
            return false;
        }
        return !in_array($column, $this->column_nosort);
    }

    /**
     * Sets the is_collapsible variable to the given boolean.
     * @param bool $bool
     * @return void
     */
    function collapsible($bool) {
        $this->is_collapsible = $bool;
    }

    /**
     * Sets the use_pages variable to the given boolean.
     * @param bool $bool
     * @return void
     */
    function pageable($bool) {
        $this->use_pages = $bool;
    }

    /**
     * Sets the use_initials variable to the given boolean.
     * @param bool $bool
     * @return void
     */
    function initialbars($bool) {
        $this->use_initials = $bool;
    }

    /**
     * Sets the pagesize variable to the given integer, the totalrows variable
     * to the given integer, and the use_pages variable to true.
     * @param int $perpage
     * @param int $total
     * @return void
     */
    function pagesize($perpage, $total) {
        $this->pagesize  = $perpage;
        $this->totalrows = $total;
        $this->use_pages = true;
    }

    /**
     * Assigns each given variable in the array to the corresponding index
     * in the request class variable.
     * @param array $variables
     * @return void
     */
    function set_control_variables($variables) {
        foreach ($variables as $what => $variable) {
            if (isset($this->request[$what])) {
                $this->request[$what] = $variable;
            }
        }
    }

    /**
     * Gives the given $value to the $attribute index of $this->attributes.
     * @param string $attribute
     * @param mixed $value
     * @return void
     */
    function set_attribute($attribute, $value) {
        $this->attributes[$attribute] = $value;
    }

    /**
     * What this method does is set the column so that if the same data appears in
     * consecutive rows, then it is not repeated.
     *
     * For example, in the quiz overview report, the fullname column is set to be suppressed, so
     * that when one student has made multiple attempts, their name is only printed in the row
     * for their first attempt.
     * @param int $column the index of a column.
     */
    function column_suppress($column) {
        if (isset($this->column_suppress[$column])) {
            $this->column_suppress[$column] = true;
        }
    }

    /**
     * Sets the given $column index to the given $classname in $this->column_class.
     * @param int $column
     * @param string $classname
     * @return void
     */
    function column_class($column, $classname) {
        if (isset($this->column_class[$column])) {
            $this->column_class[$column] = ' '.$classname; // This space needed so that classnames don't run together in the HTML
        }
    }

    /**
     * Sets the given $column index and $property index to the given $value in $this->column_style.
     * @param int $column
     * @param string $property
     * @param mixed $value
     * @return void
     */
    function column_style($column, $property, $value) {
        if (isset($this->column_style[$column])) {
            $this->column_style[$column][$property] = $value;
        }
    }

    /**
     * Sets all columns' $propertys to the given $value in $this->column_style.
     * @param int $property
     * @param string $value
     * @return void
     */
    function column_style_all($property, $value) {
        foreach (array_keys($this->columns) as $column) {
            $this->column_style[$column][$property] = $value;
        }
    }

    /**
     * Sets $this->baseurl.
     * @param moodle_url|string $url the url with params needed to call up this page
     */
    function define_baseurl($url) {
        $this->baseurl = new moodle_url($url);
    }

    /**
     * @param array $columns an array of identifying names for columns. If
     * columns are sorted then column names must correspond to a field in sql.
     */
    function define_columns($columns) {
        $this->columns = array();
        $this->column_style = array();
        $this->column_class = array();
        $colnum = 0;

        foreach ($columns as $column) {
            $this->columns[$column]         = $colnum++;
            $this->column_style[$column]    = array();
            $this->column_class[$column]    = '';
            $this->column_suppress[$column] = false;
        }
    }

    /**
     * @param array $headers numerical keyed array of displayed string titles
     * for each column.
     */
    function define_headers($headers) {
        $this->headers = $headers;
    }

    /**
     * Must be called after table is defined. Use methods above first. Cannot
     * use functions below till after calling this method.
     * @return type?
     */
    function setup() {
        global $SESSION, $CFG;

        if (empty($this->columns) || empty($this->uniqueid)) {
            return false;
        }

        if (!isset($SESSION->flextable)) {
            $SESSION->flextable = array();
        }

        if (!isset($SESSION->flextable[$this->uniqueid])) {
            $SESSION->flextable[$this->uniqueid] = new stdClass;
            $SESSION->flextable[$this->uniqueid]->uniqueid = $this->uniqueid;
            $SESSION->flextable[$this->uniqueid]->collapse = array();
            $SESSION->flextable[$this->uniqueid]->sortby   = array();
            $SESSION->flextable[$this->uniqueid]->i_first  = '';
            $SESSION->flextable[$this->uniqueid]->i_last   = '';
            $SESSION->flextable[$this->uniqueid]->textsort = $this->column_textsort;
        }

        $this->sess = &$SESSION->flextable[$this->uniqueid];

        if (($showcol = optional_param($this->request[TABLE_VAR_SHOW], '', PARAM_ALPHANUMEXT)) &&
                isset($this->columns[$showcol])) {
            $this->sess->collapse[$showcol] = false;

        } else if (($hidecol = optional_param($this->request[TABLE_VAR_HIDE], '', PARAM_ALPHANUMEXT)) &&
                isset($this->columns[$hidecol])) {
            $this->sess->collapse[$hidecol] = true;
            if (array_key_exists($hidecol, $this->sess->sortby)) {
                unset($this->sess->sortby[$hidecol]);
            }
        }

        // Now, update the column attributes for collapsed columns
        foreach (array_keys($this->columns) as $column) {
            if (!empty($this->sess->collapse[$column])) {
                $this->column_style[$column]['width'] = '10px';
            }
        }

        if (($sortcol = optional_param($this->request[TABLE_VAR_SORT], '', PARAM_ALPHANUMEXT)) &&
                $this->is_sortable($sortcol) && empty($this->sess->collapse[$sortcol]) &&
                (isset($this->columns[$sortcol]) || in_array($sortcol, array('firstname', 'lastname')) && isset($this->columns['fullname']))) {

            if (array_key_exists($sortcol, $this->sess->sortby)) {
                // This key already exists somewhere. Change its sortorder and bring it to the top.
                $sortorder = $this->sess->sortby[$sortcol] == SORT_ASC ? SORT_DESC : SORT_ASC;
                unset($this->sess->sortby[$sortcol]);
                $this->sess->sortby = array_merge(array($sortcol => $sortorder), $this->sess->sortby);
            } else {
                // Key doesn't exist, so just add it to the beginning of the array, ascending order
                $this->sess->sortby = array_merge(array($sortcol => SORT_ASC), $this->sess->sortby);
            }

            // Finally, make sure that no more than $this->maxsortkeys are present into the array
            $this->sess->sortby = array_slice($this->sess->sortby, 0, $this->maxsortkeys);
        }

        // MDL-35375 - If a default order is defined and it is not in the current list of order by columns, add it at the end.
        // This prevents results from being returned in a random order if the only order by column contains equal values.
        if (!empty($this->sort_default_column))  {
            if (!array_key_exists($this->sort_default_column, $this->sess->sortby)) {
                $defaultsort = array($this->sort_default_column => $this->sort_default_order);
                $this->sess->sortby = array_merge($this->sess->sortby, $defaultsort);
            }
        }

        $ilast = optional_param($this->request[TABLE_VAR_ILAST], null, PARAM_RAW);
        if (!is_null($ilast) && ($ilast ==='' || strpos(get_string('alphabet', 'langconfig'), $ilast) !== false)) {
            $this->sess->i_last = $ilast;
        }

        $ifirst = optional_param($this->request[TABLE_VAR_IFIRST], null, PARAM_RAW);
        if (!is_null($ifirst) && ($ifirst === '' || strpos(get_string('alphabet', 'langconfig'), $ifirst) !== false)) {
            $this->sess->i_first = $ifirst;
        }

        if (empty($this->baseurl)) {
            debugging('You should set baseurl when using flexible_table.');
            global $PAGE;
            $this->baseurl = $PAGE->url;
        }

        $this->currpage = optional_param($this->request[TABLE_VAR_PAGE], 0, PARAM_INT);
        $this->setup = true;

        // Always introduce the "flexible" class for the table if not specified
        if (empty($this->attributes)) {
            $this->attributes['class'] = 'flexible';
        } else if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = 'flexible';
        } else if (!in_array('flexible', explode(' ', $this->attributes['class']))) {
            $this->attributes['class'] = trim('flexible ' . $this->attributes['class']);
        }
    }

    /**
     * Get the order by clause from the session, for the table with id $uniqueid.
     * @param string $uniqueid the identifier for a table.
     * @return SQL fragment that can be used in an ORDER BY clause.
     */
    public static function get_sort_for_table($uniqueid) {
        global $SESSION;
        if (empty($SESSION->flextable[$uniqueid])) {
           return '';
        }

        $sess = &$SESSION->flextable[$uniqueid];
        if (empty($sess->sortby)) {
            return '';
        }
        if (empty($sess->textsort)) {
            $sess->textsort = array();
        }

        return self::construct_order_by($sess->sortby, $sess->textsort);
    }

    /**
     * Prepare an an order by clause from the list of columns to be sorted.
     * @param array $cols column name => SORT_ASC or SORT_DESC
     * @return SQL fragment that can be used in an ORDER BY clause.
     */
    public static function construct_order_by($cols, $textsortcols=array()) {
        global $DB;
        $bits = array();

        foreach ($cols as $column => $order) {
            if (in_array($column, $textsortcols)) {
                $column = $DB->sql_order_by_text($column);
            }
            if ($order == SORT_ASC) {
                $bits[] = $column . ' ASC';
            } else {
                $bits[] = $column . ' DESC';
            }
        }

        return implode(', ', $bits);
    }

    /**
     * @return SQL fragment that can be used in an ORDER BY clause.
     */
    public function get_sql_sort() {
        return self::construct_order_by($this->get_sort_columns(), $this->column_textsort);
    }

    /**
     * Get the columns to sort by, in the form required by {@link construct_order_by()}.
     * @return array column name => SORT_... constant.
     */
    public function get_sort_columns() {
        if (!$this->setup) {
            throw new coding_exception('Cannot call get_sort_columns until you have called setup.');
        }

        if (empty($this->sess->sortby)) {
            return array();
        }

        foreach ($this->sess->sortby as $column => $notused) {
            if (isset($this->columns[$column])) {
                continue; // This column is OK.
            }
            if (in_array($column, array('firstname', 'lastname')) &&
                    isset($this->columns['fullname'])) {
                continue; // This column is OK.
            }
            // This column is not OK.
            unset($this->sess->sortby[$column]);
        }

        return $this->sess->sortby;
    }

    /**
     * @return int the offset for LIMIT clause of SQL
     */
    function get_page_start() {
        if (!$this->use_pages) {
            return '';
        }
        return $this->currpage * $this->pagesize;
    }

    /**
     * @return int the pagesize for LIMIT clause of SQL
     */
    function get_page_size() {
        if (!$this->use_pages) {
            return '';
        }
        return $this->pagesize;
    }

    /**
     * @return string sql to add to where statement.
     */
    function get_sql_where() {
        global $DB;

        $conditions = array();
        $params = array();

        if (isset($this->columns['fullname'])) {
            static $i = 0;
            $i++;

            if (!empty($this->sess->i_first)) {
                $conditions[] = $DB->sql_like('firstname', ':ifirstc'.$i, false, false);
                $params['ifirstc'.$i] = $this->sess->i_first.'%';
            }
            if (!empty($this->sess->i_last)) {
                $conditions[] = $DB->sql_like('lastname', ':ilastc'.$i, false, false);
                $params['ilastc'.$i] = $this->sess->i_last.'%';
            }
        }

        return array(implode(" AND ", $conditions), $params);
    }

    /**
     * Add a row of data to the table. This function takes an array with
     * column names as keys.
     * It ignores any elements with keys that are not defined as columns. It
     * puts in empty strings into the row when there is no element in the passed
     * array corresponding to a column in the table. It puts the row elements in
     * the proper order.
     * @param $rowwithkeys array
     * @param string $classname CSS class name to add to this row's tr tag.
     */
    function add_data_keyed($rowwithkeys, $classname = '') {
        $this->add_data($this->get_row_from_keyed($rowwithkeys), $classname);
    }

    /**
     * Add a seperator line to table.
     */
    function add_separator() {
        if (!$this->setup) {
            return false;
        }
        $this->add_data(NULL);
    }

    /**
     * This method actually directly echoes the row passed to it now or adds it
     * to the download. If this is the first row and start_output has not
     * already been called this method also calls start_output to open the table
     * or send headers for the downloaded.
     * Can be used as before. print_html now calls finish_html to close table.
     *
     * @param array $row a numerically keyed row of data to add to the table.
     * @param string $classname CSS class name to add to this row's tr tag.
     * @return bool success.
     */
    function add_data($row, $classname = '') {
        if (!$this->setup) {
            return false;
        }
        if (!$this->started_output) {
            $this->start_output();
        }
        if ($this->exportclass!==null) {
            if ($row === null) {
                $this->exportclass->add_seperator();
            } else {
                $this->exportclass->add_data($row);
            }
        } else {
            $this->print_row($row, $classname);
        }
        return true;
    }

    /**
     * You should call this to finish outputting the table data after adding
     * data to the table with add_data or add_data_keyed.
     *
     */
    function finish_output($closeexportclassdoc = true) {
        if ($this->exportclass!==null) {
            $this->exportclass->finish_table();
            if ($closeexportclassdoc) {
                $this->exportclass->finish_document();
            }
        } else {
            $this->finish_html();
        }
    }

    /**
     * Hook that can be overridden in child classes to wrap a table in a form
     * for example. Called only when there is data to display and not
     * downloading.
     */
    function wrap_html_start() {
    }

    /**
     * Hook that can be overridden in child classes to wrap a table in a form
     * for example. Called only when there is data to display and not
     * downloading.
     */
    function wrap_html_finish() {
    }

    /**
     *
     * @param array $row row of data from db used to make one row of the table.
     * @return array one row for the table, added using add_data_keyed method.
     */
    function format_row($row) {
        $formattedrow = array();
        foreach (array_keys($this->columns) as $column) {
            $colmethodname = 'col_'.$column;
            if (method_exists($this, $colmethodname)) {
                $formattedcolumn = $this->$colmethodname($row);
            } else {
                $formattedcolumn = $this->other_cols($column, $row);
                if ($formattedcolumn===NULL) {
                    $formattedcolumn = $row->$column;
                }
            }
            $formattedrow[$column] = $formattedcolumn;
        }
        return $formattedrow;
    }

    /**
     * Fullname is treated as a special columname in tablelib and should always
     * be treated the same as the fullname of a user.
     * @uses $this->useridfield if the userid field is not expected to be id
     * then you need to override $this->useridfield to point at the correct
     * field for the user id.
     *
     */
    function col_fullname($row) {
        global $COURSE, $CFG;

        $name = fullname($row);
        if ($this->download) {
            return $name;
        }

        $userid = $row->{$this->useridfield};
        if ($COURSE->id == SITEID) {
            $profileurl = new moodle_url('/user/profile.php', array('id' => $userid));
        } else {
            $profileurl = new moodle_url('/user/view.php',
                    array('id' => $userid, 'course' => $COURSE->id));
        }
        return html_writer::link($profileurl, $name);
    }

    /**
     * You can override this method in a child class. See the description of
     * build_table which calls this method.
     */
    function other_cols($column, $row) {
        return NULL;
    }

    /**
     * Used from col_* functions when text is to be displayed. Does the
     * right thing - either converts text to html or strips any html tags
     * depending on if we are downloading and what is the download type. Params
     * are the same as format_text function in weblib.php but some default
     * options are changed.
     */
    function format_text($text, $format=FORMAT_MOODLE, $options=NULL, $courseid=NULL) {
        if (!$this->is_downloading()) {
            if (is_null($options)) {
                $options = new stdClass;
            }
            //some sensible defaults
            if (!isset($options->para)) {
                $options->para = false;
            }
            if (!isset($options->newlines)) {
                $options->newlines = false;
            }
            if (!isset($options->smiley)) {
                $options->smiley = false;
            }
            if (!isset($options->filter)) {
                $options->filter = false;
            }
            return format_text($text, $format, $options);
        } else {
            $eci =& $this->export_class_instance();
            return $eci->format_text($text, $format, $options, $courseid);
        }
    }
    /**
     * This method is deprecated although the old api is still supported.
     * @deprecated 1.9.2 - Jun 2, 2008
     */
    function print_html() {
        if (!$this->setup) {
            return false;
        }
        $this->finish_html();
    }

    /**
     * This function is not part of the public api.
     * @return string initial of first name we are currently filtering by
     */
    function get_initial_first() {
        if (!$this->use_initials) {
            return NULL;
        }

        return $this->sess->i_first;
    }

    /**
     * This function is not part of the public api.
     * @return string initial of last name we are currently filtering by
     */
    function get_initial_last() {
        if (!$this->use_initials) {
            return NULL;
        }

        return $this->sess->i_last;
    }

    /**
     * Helper function, used by {@link print_initials_bar()} to output one initial bar.
     * @param array $alpha of letters in the alphabet.
     * @param string $current the currently selected letter.
     * @param string $class class name to add to this initial bar.
     * @param string $title the name to put in front of this initial bar.
     * @param string $urlvar URL parameter name for this initial.
     */
    protected function print_one_initials_bar($alpha, $current, $class, $title, $urlvar) {
        echo html_writer::start_tag('div', array('class' => 'initialbar paging ' . $class));
        
        echo html_writer::tag('span', $title.':');
        
        if ($current) {
        	echo html_writer::link($this->baseurl->out(false, array($urlvar => '')), get_string('all') , array('class'=>'btn'));
        } else {
        	echo html_writer::link($this->baseurl->out(false, array($urlvar => '')), get_string('all') , array('class'=>'btn selected'));
//            echo html_writer::tag('strong', get_string('all'));
        }

        foreach ($alpha as $letter) {
            if ($letter === $current) {
                echo html_writer::link($this->baseurl->out(false, array($urlvar => $letter)), $letter , array('class'=>'btn selected'));
            } else {
                echo html_writer::link($this->baseurl->out(false, array($urlvar => $letter)), $letter , array('class'=>'btn'));
            }
        }

        echo html_writer::end_tag('div');
    }

    /**
     * This function is not part of the public api.
     */
    function print_initials_bar() {
        if ((!empty($this->sess->i_last) || !empty($this->sess->i_first) ||$this->use_initials)
                    && isset($this->columns['fullname'])) {

            $alpha  = explode(',', get_string('alphabet', 'langconfig'));

            // Bar of first initials
            if (!empty($this->sess->i_first)) {
                $ifirst = $this->sess->i_first;
            } else {
                $ifirst = '';
            }
            $this->print_one_initials_bar($alpha, $ifirst, 'firstinitial',
                    get_string('firstname'), $this->request[TABLE_VAR_IFIRST]);

            // Bar of last initials
            if (!empty($this->sess->i_last)) {
                $ilast = $this->sess->i_last;
            } else {
                $ilast = '';
            }
            $this->print_one_initials_bar($alpha, $ilast, 'lastinitial',
                    get_string('lastname'), $this->request[TABLE_VAR_ILAST]);
        }
    }

    /**
     * This function is not part of the public api.
     */
    function print_nothing_to_display() {
        global $OUTPUT;
        $this->print_initials_bar();

        echo $OUTPUT->heading(get_string('nothingtodisplay'));
    }

    /**
     * This function is not part of the public api.
     */
    function get_row_from_keyed($rowwithkeys) {
        if (is_object($rowwithkeys)) {
            $rowwithkeys = (array)$rowwithkeys;
        }
        $row = array();
        foreach (array_keys($this->columns) as $column) {
            if (isset($rowwithkeys[$column])) {
                $row [] = $rowwithkeys[$column];
            } else {
                $row[] ='';
            }
        }
        return $row;
    }
    /**
     * This function is not part of the public api.
     */
    function get_download_menu() {
        $allclasses= get_declared_classes();
        $exportclasses = array();
        foreach ($allclasses as $class) {
            $matches = array();
            if (preg_match('/^table\_([a-z]+)\_export\_format$/', $class, $matches)) {
                $type = $matches[1];
                $exportclasses[$type]= get_string("download$type", 'table');
            }
        }
        return $exportclasses;
    }

    /**
     * This function is not part of the public api.
     */
    function download_buttons() {
        if ($this->is_downloadable() && !$this->is_downloading()) {
            $downloadoptions = $this->get_download_menu();

            $downloadelements = new stdClass();
            $downloadelements->formatsmenu = html_writer::select($downloadoptions,
                    'download', $this->defaultdownloadformat, false);
            $downloadelements->downloadbutton = '<input type="submit" value="'.
                    get_string('download').'"/>';
            $html = '<form action="'. $this->baseurl .'" method="post">';
            $html .= '<div class="mdl-align">';
            $html .= html_writer::tag('label', get_string('downloadas', 'table', $downloadelements));
            $html .= '</div></form>';

            return $html;
        } else {
            return '';
        }
    }
    /**
     * This function is not part of the public api.
     * You don't normally need to call this. It is called automatically when
     * needed when you start adding data to the table.
     *
     */
    function start_output() {
        $this->started_output = true;
        if ($this->exportclass!==null) {
            $this->exportclass->start_table($this->sheettitle);
            $this->exportclass->output_headers($this->headers);
        } else {
            $this->start_html();
            $this->print_headers();
            echo html_writer::start_tag('tbody');
        }
    }

    /**
     * This function is not part of the public api.
     */
    function print_row($row, $classname = '') {
        static $suppress_lastrow = NULL;
        $oddeven = $this->currentrow % 2;
        $rowclasses = array('r' . $oddeven);

        if ($classname) {
            $rowclasses[] = $classname;
        }

        $rowid = $this->uniqueid . '_r' . $this->currentrow;

        echo html_writer::start_tag('tr', array('class' => implode(' ', $rowclasses), 'id' => $rowid));

        // If we have a separator, print it
        if ($row === NULL) {
            $colcount = count($this->columns);
            echo html_writer::tag('td', html_writer::tag('div', '',
                    array('class' => 'tabledivider')), array('colspan' => $colcount));

        } else {
            $colbyindex = array_flip($this->columns);
            foreach ($row as $index => $data) {
                $column = $colbyindex[$index];

                if (empty($this->sess->collapse[$column])) {
                    if ($this->column_suppress[$column] && $suppress_lastrow !== NULL && $suppress_lastrow[$index] === $data) {
                        $content = '&nbsp;';
                    } else {
                        $content = $data;
                    }
                } else {
                    $content = '&nbsp;';
                }

                echo html_writer::tag('td', $content, array(
                        'class' => 'cell c' . $index . $this->column_class[$column],
                        'id' => $rowid . '_c' . $index,
                        'style' => $this->make_styles_string($this->column_style[$column])));
            }
        }

        echo html_writer::end_tag('tr');

        $suppress_enabled = array_sum($this->column_suppress);
        if ($suppress_enabled) {
            $suppress_lastrow = $row;
        }
        $this->currentrow++;
    }

    /**
     * This function is not part of the public api.
     */
    function finish_html() {
        global $OUTPUT;
        if (!$this->started_output) {
            //no data has been added to the table.
            $this->print_nothing_to_display();

        } else {
            // Print empty rows to fill the table to the current pagesize.
            // This is done so the header aria-controls attributes do not point to
            // non existant elements.
            $emptyrow = array_fill(0, count($this->columns), '');
            while ($this->currentrow < $this->pagesize) {
                $this->print_row($emptyrow, 'emptyrow');
            }

            echo html_writer::end_tag('tbody');
            echo html_writer::end_tag('table');
            echo html_writer::end_tag('div');
            $this->wrap_html_finish();

            // Paging bar
            if(in_array(TABLE_P_BOTTOM, $this->showdownloadbuttonsat)) {
                echo $this->download_buttons();
            }

            if($this->use_pages) {
                $pagingbar = new paging_bar($this->totalrows, $this->currpage, $this->pagesize, $this->baseurl);
                $pagingbar->pagevar = $this->request[TABLE_VAR_PAGE];
                echo $OUTPUT->render($pagingbar);
            }
        }
    }

    /**
     * Generate the HTML for the collapse/uncollapse icon. This is a helper method
     * used by {@link print_headers()}.
     * @param string $column the column name, index into various names.
     * @param int $index numerical index of the column.
     * @return string HTML fragment.
     */
    protected function show_hide_link($column, $index) {
        global $OUTPUT;
        // Some headers contain <br /> tags, do not include in title, hence the
        // strip tags.

        $ariacontrols = '';
        for ($i = 0; $i < $this->pagesize; $i++) {
            $ariacontrols .= $this->uniqueid . '_r' . $i . '_c' . $index . ' ';
        }

        $ariacontrols = trim($ariacontrols);

        if (!empty($this->sess->collapse[$column])) {
            $linkattributes = array('title' => get_string('show') . ' ' . strip_tags($this->headers[$index]),
                                    'aria-expanded' => 'false',
                                    'aria-controls' => $ariacontrols);
            return html_writer::link($this->baseurl->out(false, array($this->request[TABLE_VAR_SHOW] => $column)),
                    html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/switch_plus'), 'alt' => get_string('show'))),
                    $linkattributes);

        } else if ($this->headers[$index] !== NULL) {
            $linkattributes = array('title' => get_string('hide') . ' ' . strip_tags($this->headers[$index]),
                                    'aria-expanded' => 'true',
                                    'aria-controls' => $ariacontrols);
            return html_writer::link($this->baseurl->out(false, array($this->request[TABLE_VAR_HIDE] => $column)),
                    html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/switch_minus'), 'alt' => get_string('hide'))),
                    $linkattributes);
        }
    }

    /**
     * This function is not part of the public api.
     */
    function print_headers() {
        global $CFG, $OUTPUT;

        echo html_writer::start_tag('thead');
        echo html_writer::start_tag('tr');
        foreach ($this->columns as $column => $index) {

            $icon_hide = '';
            if ($this->is_collapsible) {
                $icon_hide = $this->show_hide_link($column, $index);
            }

            $primary_sort_column = '';
            $primary_sort_order  = '';
            if (reset($this->sess->sortby)) {
                $primary_sort_column = key($this->sess->sortby);
                $primary_sort_order  = current($this->sess->sortby);
            }

            switch ($column) {

                case 'fullname':
                if ($this->is_sortable($column)) {
                    $firstnamesortlink = $this->sort_link(get_string('firstname'),
                            'firstname', $primary_sort_column === 'firstname', $primary_sort_order);

                    $lastnamesortlink = $this->sort_link(get_string('lastname'),
                            'lastname', $primary_sort_column === 'lastname', $primary_sort_order);

                    $override = new stdClass();
                    $override->firstname = 'firstname';
                    $override->lastname = 'lastname';
                    $fullnamelanguage = get_string('fullnamedisplay', '', $override);

                    if (($CFG->fullnamedisplay == 'firstname lastname') or
                        ($CFG->fullnamedisplay == 'firstname') or
                        ($CFG->fullnamedisplay == 'language' and $fullnamelanguage == 'firstname lastname' )) {
                        $this->headers[$index] = $firstnamesortlink . ' / ' . $lastnamesortlink;
                    } else {
                        $this->headers[$index] = $lastnamesortlink . ' / ' . $firstnamesortlink;
                    }
                }
                break;

                case 'userpic':
                    // do nothing, do not display sortable links
                break;

                default:
                if ($this->is_sortable($column)) {
                    $this->headers[$index] = $this->sort_link($this->headers[$index],
                            $column, $primary_sort_column == $column, $primary_sort_order);
                }
            }

            $attributes = array(
                'class' => 'header c' . $index . $this->column_class[$column],
                'scope' => 'col',
            );
            if ($this->headers[$index] === NULL) {
                $content = '&nbsp;';
            } else if (!empty($this->sess->collapse[$column])) {
                $content = $icon_hide;
            } else {
                if (is_array($this->column_style[$column])) {
                    $attributes['style'] = $this->make_styles_string($this->column_style[$column]);
                }
                $content = $this->headers[$index] . html_writer::tag('div',
                        $icon_hide, array('class' => 'commands'));
            }
            echo html_writer::tag('th', $content, $attributes);
        }

        echo html_writer::end_tag('tr');
        echo html_writer::end_tag('thead');
    }

    /**
     * Generate the HTML for the sort icon. This is a helper method used by {@link sort_link()}.
     * @param bool $isprimary whether an icon is needed (it is only needed for the primary sort column.)
     * @param int $order SORT_ASC or SORT_DESC
     * @return string HTML fragment.
     */
    protected function sort_icon($isprimary, $order) {
        global $OUTPUT;

        if (!$isprimary) {
            return '';
        }

        if ($order == SORT_ASC) {
            return html_writer::empty_tag('img',
                    array('src' => $OUTPUT->pix_url('t/sort_asc'), 'alt' => get_string('asc'), 'class' => 'iconsort'));
        } else {
            return html_writer::empty_tag('img',
                    array('src' => $OUTPUT->pix_url('t/sort_desc'), 'alt' => get_string('desc'), 'class' => 'iconsort'));
        }
    }

    /**
     * Generate the correct tool tip for changing the sort order. This is a
     * helper method used by {@link sort_link()}.
     * @param bool $isprimary whether the is column is the current primary sort column.
     * @param int $order SORT_ASC or SORT_DESC
     * @return string the correct title.
     */
    protected function sort_order_name($isprimary, $order) {
        if ($isprimary && $order != SORT_ASC) {
            return get_string('desc');
        } else {
            return get_string('asc');
        }
    }

    /**
     * Generate the HTML for the sort link. This is a helper method used by {@link print_headers()}.
     * @param string $text the text for the link.
     * @param string $column the column name, may be a fake column like 'firstname' or a real one.
     * @param bool $isprimary whether the is column is the current primary sort column.
     * @param int $order SORT_ASC or SORT_DESC
     * @return string HTML fragment.
     */
    protected function sort_link($text, $column, $isprimary, $order) {
        return html_writer::link($this->baseurl->out(false,
                array($this->request[TABLE_VAR_SORT] => $column)),
                $text . get_accesshide(get_string('sortby') . ' ' .
                $text . ' ' . $this->sort_order_name($isprimary, $order))) . ' ' .
                $this->sort_icon($isprimary, $order);
    }

    /**
     * This function is not part of the public api.
     */
    function start_html() {
        global $OUTPUT;
        // Do we need to print initial bars?
        $this->print_initials_bar();

        // Paging bar
        if ($this->use_pages) {
            $pagingbar = new paging_bar($this->totalrows, $this->currpage, $this->pagesize, $this->baseurl);
            $pagingbar->pagevar = $this->request[TABLE_VAR_PAGE];
            echo $OUTPUT->render($pagingbar);
        }

        if (in_array(TABLE_P_TOP, $this->showdownloadbuttonsat)) {
            echo $this->download_buttons();
        }

        $this->wrap_html_start();
        // Start of main data table

        echo html_writer::start_tag('div', array('class' => 'no-overflow'));
        echo html_writer::start_tag('table', $this->attributes);

    }

    /**
     * This function is not part of the public api.
     * @param array $styles CSS-property => value
     * @return string values suitably to go in a style="" attribute in HTML.
     */
    function make_styles_string($styles) {
        if (empty($styles)) {
            return null;
        }

        $string = '';
        foreach($styles as $property => $value) {
            $string .= $property . ':' . $value . ';';
        }
        return $string;
    }
}


/**
 * @package   moodlecore
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table_sql extends flexible_table {

    public $countsql = NULL;
    public $countparams = NULL;
    /**
     * @var object sql for querying db. Has fields 'fields', 'from', 'where', 'params'.
     */
    public $sql = NULL;
    /**
     * @var array Data fetched from the db.
     */
    public $rawdata = NULL;

    /**
     * @var bool Overriding default for this.
     */
    public $is_sortable    = true;
    /**
     * @var bool Overriding default for this.
     */
    public $is_collapsible = true;

    /**
     * @param string $uniqueid a string identifying this table.Used as a key in
     *                          session  vars.
     */
    function __construct($uniqueid) {
        parent::__construct($uniqueid);
        // some sensible defaults
        $this->set_attribute('cellspacing', '0');
        $this->set_attribute('class', 'generaltable generalbox');
    }

    /**
     * Take the data returned from the db_query and go through all the rows
     * processing each col using either col_{columnname} method or other_cols
     * method or if other_cols returns NULL then put the data straight into the
     * table.
     */
    function build_table() {
        if ($this->rawdata) {
            foreach ($this->rawdata as $row) {
                $formattedrow = $this->format_row($row);
                $this->add_data_keyed($formattedrow,
                        $this->get_row_class($row));
            }
        }
    }

    /**
     * Get any extra classes names to add to this row in the HTML.
     * @param $row array the data for this row.
     * @return string added to the class="" attribute of the tr.
     */
    function get_row_class($row) {
        return '';
    }

    /**
     * This is only needed if you want to use different sql to count rows.
     * Used for example when perhaps all db JOINS are not needed when counting
     * records. You don't need to call this function the count_sql
     * will be generated automatically.
     *
     * We need to count rows returned by the db seperately to the query itself
     * as we need to know how many pages of data we have to display.
     */
    function set_count_sql($sql, array $params = NULL) {
        $this->countsql = $sql;
        $this->countparams = $params;
    }

    /**
     * Set the sql to query the db. Query will be :
     *      SELECT $fields FROM $from WHERE $where
     * Of course you can use sub-queries, JOINS etc. by putting them in the
     * appropriate clause of the query.
     */
    function set_sql($fields, $from, $where, array $params = NULL) {
        $this->sql = new stdClass();
        $this->sql->fields = $fields;
        $this->sql->from = $from;
        $this->sql->where = $where;
        $this->sql->params = $params;
    }

    /**
     * Query the db. Store results in the table object for use by build_table.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar. Bar
     * will only be used if there is a fullname column defined for the table.
     */
    function query_db($pagesize, $useinitialsbar=true) {
        global $DB;
        if (!$this->is_downloading()) {
            if ($this->countsql === NULL) {
                $this->countsql = 'SELECT COUNT(1) FROM '.$this->sql->from.' WHERE '.$this->sql->where;
                $this->countparams = $this->sql->params;
            }
            $grandtotal = $DB->count_records_sql($this->countsql, $this->countparams);
            if ($useinitialsbar && !$this->is_downloading()) {
                $this->initialbars($grandtotal > $pagesize);
            }

            list($wsql, $wparams) = $this->get_sql_where();
            if ($wsql) {
                $this->countsql .= ' AND '.$wsql;
                $this->countparams = array_merge($this->countparams, $wparams);

                $this->sql->where .= ' AND '.$wsql;
                $this->sql->params = array_merge($this->sql->params, $wparams);

                $total  = $DB->count_records_sql($this->countsql, $this->countparams);
            } else {
                $total = $grandtotal;
            }

            $this->pagesize($pagesize, $total);
        }

        // Fetch the attempts
        $sort = $this->get_sql_sort();
        if ($sort) {
            $sort = "ORDER BY $sort";
        }
        $sql = "SELECT
                {$this->sql->fields}
                FROM {$this->sql->from}
                WHERE {$this->sql->where}
                {$sort}";

        if (!$this->is_downloading()) {
            $this->rawdata = $DB->get_records_sql($sql, $this->sql->params, $this->get_page_start(), $this->get_page_size());
        } else {
            $this->rawdata = $DB->get_records_sql($sql, $this->sql->params);
        }
    }

    /**
     * Convenience method to call a number of methods for you to display the
     * table.
     */
    function out($pagesize, $useinitialsbar, $downloadhelpbutton='') {
        global $DB;
        if (!$this->columns) {
            $onerow = $DB->get_record_sql("SELECT {$this->sql->fields} FROM {$this->sql->from} WHERE {$this->sql->where}", $this->sql->params);
            //if columns is not set then define columns as the keys of the rows returned
            //from the db.
            $this->define_columns(array_keys((array)$onerow));
            $this->define_headers(array_keys((array)$onerow));
        }
        $this->setup();
        $this->query_db($pagesize, $useinitialsbar);
        $this->build_table();
        $this->finish_output();
    }
}


/**
 * @package   moodlecore
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table_default_export_format_parent {
    /**
     * @var flexible_table or child class reference pointing to table class
     * object from which to export data.
     */
    var $table;

    /**
     * @var bool output started. Keeps track of whether any output has been
     * started yet.
     */
    var $documentstarted = false;
    function table_default_export_format_parent(&$table) {
        $this->table =& $table;
    }

    function set_table(&$table) {
        $this->table =& $table;
    }

    function add_data($row) {
        return false;
    }

    function add_seperator() {
        return false;
    }

    function document_started() {
        return $this->documentstarted;
    }
    /**
     * Given text in a variety of format codings, this function returns
     * the text as safe HTML or as plain text dependent on what is appropriate
     * for the download format. The default removes all tags.
     */
    function format_text($text, $format=FORMAT_MOODLE, $options=NULL, $courseid=NULL) {
        //use some whitespace to indicate where there was some line spacing.
        $text = str_replace(array('</p>', "\n", "\r"), '   ', $text);
        return strip_tags($text);
    }
}


/**
 * @package   moodlecore
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table_spreadsheet_export_format_parent extends table_default_export_format_parent {
    var $currentrow;
    var $workbook;
    var $worksheet;
    /**
     * @var object format object - format for normal table cells
     */
    var $formatnormal;
    /**
     * @var object format object - format for header table cells
     */
    var $formatheaders;

    /**
     * should be overriden in child class.
     */
    var $fileextension;

    /**
     * This method will be overridden in the child class.
     */
    function define_workbook() {
    }

    function start_document($filename) {
        $filename = $filename.'.'.$this->fileextension;
        $this->define_workbook();
        // format types
        $this->formatnormal =& $this->workbook->add_format();
        $this->formatnormal->set_bold(0);
        $this->formatheaders =& $this->workbook->add_format();
        $this->formatheaders->set_bold(1);
        $this->formatheaders->set_align('center');
        // Sending HTTP headers
        $this->workbook->send($filename);
        $this->documentstarted = true;
    }

    function start_table($sheettitle) {
        $this->worksheet = $this->workbook->add_worksheet($sheettitle);
        $this->currentrow=0;
    }

    function output_headers($headers) {
        $colnum = 0;
        foreach ($headers as $item) {
            $this->worksheet->write($this->currentrow,$colnum,$item,$this->formatheaders);
            $colnum++;
        }
        $this->currentrow++;
    }

    function add_data($row) {
        $colnum = 0;
        foreach ($row as $item) {
            $this->worksheet->write($this->currentrow,$colnum,$item,$this->formatnormal);
            $colnum++;
        }
        $this->currentrow++;
        return true;
    }

    function add_seperator() {
        $this->currentrow++;
        return true;
    }

    function finish_table() {
    }

    function finish_document() {
        $this->workbook->close();
        exit;
    }
}


/**
 * @package   moodlecore
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table_excel_export_format extends table_spreadsheet_export_format_parent {
    var $fileextension = 'xls';

    function define_workbook() {
        global $CFG;
        require_once("$CFG->libdir/excellib.class.php");
        // Creating a workbook
        $this->workbook = new MoodleExcelWorkbook("-");
    }

}


/**
 * @package   moodlecore
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table_ods_export_format extends table_spreadsheet_export_format_parent {
    var $fileextension = 'ods';
    function define_workbook() {
        global $CFG;
        require_once("$CFG->libdir/odslib.class.php");
        // Creating a workbook
        $this->workbook = new MoodleODSWorkbook("-");
    }
}


/**
 * @package   moodlecore
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table_text_export_format_parent extends table_default_export_format_parent {
    protected $seperator = "tab";
    protected $mimetype = 'text/tab-separated-values';
    protected $ext = '.txt';
    protected $myexporter;

    public function __construct() {
        $this->myexporter = new csv_export_writer($this->seperator, '"', $this->mimetype);
    }

    public function start_document($filename) {
        $this->filename = $filename;
        $this->documentstarted = true;
        $this->myexporter->set_filename($filename, $this->ext);
    }

    public function start_table($sheettitle) {
        //nothing to do here
    }

    public function output_headers($headers) {
        $this->myexporter->add_data($headers);
    }

    public function add_data($row) {
        $this->myexporter->add_data($row);
        return true;
    }

    public function finish_table() {
        //nothing to do here
    }

    public function finish_document() {
        $this->myexporter->download_file();
        exit;
    }

    /**
     * Format a row of data.
     * @param array $data
     */
    protected function format_row($data) {
        $escapeddata = array();
        foreach ($data as $value) {
            $escapeddata[] = '"' . str_replace('"', '""', $value) . '"';
        }
        return implode($this->seperator, $escapeddata) . "\n";
    }
}


/**
 * @package   moodlecore
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table_tsv_export_format extends table_text_export_format_parent {
    protected $seperator = "tab";
    protected $mimetype = 'text/tab-separated-values';
    protected $ext = '.txt';
}

require_once($CFG->libdir . '/csvlib.class.php');
/**
 * @package   moodlecore
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table_csv_export_format extends table_text_export_format_parent {
    protected $seperator = "comma";
    protected $mimetype = 'text/csv';
    protected $ext = '.csv';
}

/**
 * @package   moodlecore
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table_xhtml_export_format extends table_default_export_format_parent {
    function start_document($filename) {
        header("Content-Type: application/download\n");
        header("Content-Disposition: attachment; filename=\"$filename.html\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
        header("Pragma: public");
        //html headers
        echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml"
  xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">/*<![CDATA[*/

.flexible th {
white-space:normal;
}
th.header, td.header, div.header {
border-color:#DDDDDD;
background-color:lightGrey;
}
.flexible th {
white-space:nowrap;
}
th {
font-weight:bold;
}

.generaltable {
border-style:solid;
}
.generalbox {
border-style:solid;
}
body, table, td, th {
font-family:Arial,Verdana,Helvetica,sans-serif;
font-size:100%;
}
td {
    border-style:solid;
    border-width:1pt;
}
table {
    border-collapse:collapse;
    border-spacing:0pt;
    width:80%;
    margin:auto;
}

h1, h2 {
    text-align:center;
}
.bold {
font-weight:bold;
}
.mdl-align {
    text-align:center;
}
/*]]>*/</style>
<title>$filename</title>
</head>
<body>
EOF;
        $this->documentstarted = true;
    }

    function start_table($sheettitle) {
        $this->table->sortable(false);
        $this->table->collapsible(false);
        echo "<h2>{$sheettitle}</h2>";
        $this->table->start_html();
    }

    function output_headers($headers) {
        $this->table->print_headers();
        echo html_writer::start_tag('tbody');
    }

    function add_data($row) {
        $this->table->print_row($row);
        return true;
    }

    function add_seperator() {
        $this->table->print_row(NULL);
        return true;
    }

    function finish_table() {
        $this->table->finish_html();
    }

    function finish_document() {
        echo "</body>\n</html>";
        exit;
    }

    function format_text($text, $format=FORMAT_MOODLE, $options=NULL, $courseid=NULL) {
        if (is_null($options)) {
            $options = new stdClass;
        }
        //some sensible defaults
        if (!isset($options->para)) {
            $options->para = false;
        }
        if (!isset($options->newlines)) {
            $options->newlines = false;
        }
        if (!isset($options->smiley)) {
            $options->smiley = false;
        }
        if (!isset($options->filter)) {
            $options->filter = false;
        }
        return format_text($text, $format, $options);
    }
}
