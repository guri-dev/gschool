<?php
class Teachers_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Teacher', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Teachers', 'sp' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );

	}


	/**
	 * Retrieve customers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_teachers( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}gs_teacher";

		// if ( ! empty( $_REQUEST['orderby'] ) ) {
		// 	$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
		// 	$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		// }

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	public static function create_teacher($data) {
		global $wpdb;

		$table = $wpdb->prefix."wp_gs_teacher";
		
		try {
			$result = $wpdb->insert(  $table, $data );
		} catch (\Throwable $th) {
			_e( 'Error occured', 'sp' );
			print_r($th); die;
		}
		$wpdb->print_error(); 
		print_r($wpdb->queries); die;
		return $result;
	}


	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_teachers( $id ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}gs_teacher",
			[ 'teachers_id' => $id ],
			[ '%d' ]
		);
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}gs_teacher";

		return $wpdb->get_var( $sql );
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No teachers avaliable.', 'sp' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'url':
				return !empty($item[ $column_name ]) ? "<img width='80' height='80' src='".$item[ $column_name ]."'>" : "<img width='80' height='80' src='".plugin_dir_url(dirname( __FILE__ ))."/images/no-img.png' alt='no image'>";
			case 'first_name':
				return $item[ $column_name ];
			case 'last_name':
				return $item[ $column_name ];
			case 'gender':
				return get_gender($item[ $column_name ]);
			case 'phone':
				return $item[ $column_name ];
			case 'email':
				return $item[ $column_name ];
			case 'createddate':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['teacher_id']
		);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'sp_delete_teachers' );

		$title = '<strong>' . $item['name'] . '</strong>';

		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			'url'    => __( 'Photo', 'gschool' ),
			'first_name'    => __( 'First Name', 'gschool' ),
			'last_name' => __( 'Last Name', 'gschool' ),
			'gender' => __( 'Gender', 'gschool' ),
			'phone' => __( 'Phone', 'gschool' ),
			'email'    => __( 'Email', 'gschool' ),
			'createddate'    => __( 'Created Date', 'gschool' )
		];

		return $columns;
	}

	

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'user_nicename' => array( 'user_nicename', true ),
			'user_login' => array( 'user_login', false )
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'customers_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_teachers( $per_page, $current_page );
	}

	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'sp_delete_teachers' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_teachers( absint( $_GET['customer'] ) );

		                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		                // add_query_arg() return the current url
		                wp_redirect( esc_url_raw(add_query_arg()) );
				exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_teachers( $id );

			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		        // add_query_arg() return the current url
		        wp_redirect( esc_url_raw(add_query_arg()) );
			exit;
		}
	}
}
?>