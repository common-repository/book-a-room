<?php
class bookaroom_settings_roomConts {
	############################################
	#
	# Room Container managment
	#
	############################################
	public static
	function bookaroom_admin_roomCont() {
		$roomContList = self::getRoomContList();
		$roomList = bookaroom_settings_rooms::getRoomList();
		$branchList = bookaroom_settings_branches::getBranchList();
		$amenityList = bookaroom_settings_amenities::getAmenityList();
		# figure out what to do
		# first, is there an action?
		$externals = self::getExternalsRoomCont();
		$error = NULL;

		switch ( $externals[ 'action' ] ) {
			case 'deleteCheck':
				if ( bookaroom_settings::checkID( $externals[ 'roomContID' ], $roomContList[ 'id' ] ) == FALSE ) {
					# show error page
					require( BOOKAROOM_PATH . 'templates/roomConts/IDerror.php' );
				} else {
					# delete room
					self::deleteRoomCont( $externals[ 'roomContID' ] );
					require( BOOKAROOM_PATH . 'templates/roomConts/deleteSuccess.php' );
				}
				break;
			case 'delete':
				# check that there is an ID and it is valid
				if ( bookaroom_settings::checkID( $externals[ 'roomContID' ], $roomContList[ 'id' ] ) == FALSE ) {
					# show error page
					require( BOOKAROOM_PATH . 'templates/roomConts/IDerror.php' );
					break;
					# check for branch and make sure it is valid
				} elseif ( empty( $roomContList[ 'id' ][ $externals[ 'roomContID' ] ][ 'branchID' ] ) or!in_array( $roomContList[ 'id' ][ $externals[ 'roomContID' ] ][ 'branchID' ], array_keys( $branchList ) ) ) {
					# show error page
					require( BOOKAROOM_PATH . 'templates/roomConts/noBranch.php' );
					break;
				} else {
					# show delete screen
					self::showRoomContDelete( $externals[ 'roomContID' ], $roomContList, $roomList, $branchList, $amenityList );
				}

				break;

			case 'editCheck':
				# check that there is an ID and it is valid
				if ( bookaroom_settings::checkID( $externals[ 'roomContID' ], $roomContList[ 'id' ] ) == FALSE ) {
					# show error page
					require( BOOKAROOM_PATH . 'templates/roomConts/IDerror.php' );
					break;
					# check for branch and make sure it is valid
				} elseif ( empty( $roomContList[ 'id' ][ $externals[ 'roomContID' ] ][ 'branchID' ] ) or!in_array( $roomContList[ 'id' ][ $externals[ 'roomContID' ] ][ 'branchID' ], array_keys( $branchList ) ) ) {
					# show error page
					require( BOOKAROOM_PATH . 'templates/roomConts/noBranch.php' );
					break;
				}

				# check entries
				if ( ( $errors = self::checkEditRoomConts( $externals, $roomContList, $branchList, $roomList, $externals[ 'roomContID' ] ) ) == NULL ) {
					self::editRoomCont( $externals, $roomList );
					require( BOOKAROOM_PATH . 'templates/roomConts/editSuccess.php' );
					break;
				} else {
					$externals[ 'errors' ] = $errors;
					$roomContInfo = self::getRoomContInfo( $externals[ 'roomContID' ] );
					self::showRoomContEdit( $externals, $roomContInfo[ 'branchID' ], $roomContList, $roomList, $branchList, $amenityList, 'editCheck', 'Edit' );
				}
				break;

			case 'edit':
				if ( bookaroom_settings::checkID( $externals[ 'roomContID' ], $roomContList[ 'id' ] ) == FALSE ) {
					# show error page
					require( BOOKAROOM_PATH . 'templates/roomConts/IDerror.php' );
					break;
					# check for branch and make sure it's valid
				} elseif ( empty( $roomContList[ 'id' ][ $externals[ 'roomContID' ] ][ 'branchID' ] ) or!in_array( $roomContList[ 'id' ][ $externals[ 'roomContID' ] ][ 'branchID' ], array_keys( $branchList ) ) ) {
					require( BOOKAROOM_PATH . 'templates/roomConts/noBranch.php' );
					break;
				}

				$roomContInfo = self::getRoomContInfo( $externals[ 'roomContID' ] );
				self::showRoomContEdit( $roomContInfo, $roomContInfo[ 'branchID' ], $roomContList, $roomList, $branchList, $amenityList, 'editCheck', 'Edit' );
				break;

			case 'addCheck':
				if ( empty( $externals[ 'branchID' ] ) or!in_array( $externals[ 'branchID' ], array_keys( $branchList ) ) ) {
					require( BOOKAROOM_PATH . 'templates/roomConts/noBranch.php' );
					break;
				}

				if ( ( $error = self::checkEditRoomConts( $externals, $roomContList, $branchList, $roomList, NULL ) ) == TRUE ) {
					$externals[ 'errors' ] = $error;
					self::showRoomContEdit( $externals, $externals[ 'branchID' ], $roomContList, $roomList, $branchList, $amenityList, 'addCheck', 'Add' );
				} else {
					self::addRoomCont( $externals, $roomList );
					require( BOOKAROOM_PATH . 'templates/roomConts/addSuccess.php' );
				}
				break;

			case 'add':
				if ( empty( $externals[ 'branchID' ] ) or!in_array( $externals[ 'branchID' ], array_keys( $branchList ) ) ) {
					require( BOOKAROOM_PATH . 'templates/roomConts/noBranch.php' );
					break;
				}
				self::showRoomContEdit( NULL, $externals[ 'branchID' ], $roomContList, $roomList, $branchList, $amenityList, 'addCheck', 'Add' );
				break;

			default:
				self::showRoomContList( $roomContList, $roomList, $branchList, $amenityList );
				break;
		}
	}

	public static
	function addRoomCont( $externals, $roomList )
	# add a new branch
	{
		global $wpdb;

		$table_name = $wpdb->prefix . "bookaroom_roomConts";
		$table_name_members = $wpdb->prefix . "bookaroom_roomConts_members";

		# make room list
		# only use valid amenity ids and serialize
		$roomArr = array_intersect( array_keys( $roomList[ 'id' ] ), $externals[ 'room' ] );

		$roomArrSQL = array();

		$isPublic = ( $externals[ 'isPublic' ] == true ) ? 1 : 0;
		$hideDaily = ( $externals[ 'hideDaily' ] == true ) ? 1 : 0;
		$final = $wpdb->insert( $table_name,
			array( 'roomCont_desc' => $externals[ 'roomContDesc' ],
				'roomCont_branch' => $externals[ 'branchID' ],
				'roomCont_isPublic' => $isPublic,
				'roomCont_hideDaily' => $hideDaily,
				'roomCont_occ' => $externals[ 'occupancy' ] ) );

		$roomContID = $wpdb->insert_id;

		foreach ( $roomArr as $val ) {
			$roomArrSQL[] = "( '{$roomContID}', '{$val}' )";
		}
		$roomSQL_final = implode( ", ", $roomArrSQL );

		$sql = "INSERT INTO `{$table_name_members}` ( `rcm_roomContID`, `rcm_roomID` ) VALUES {$roomSQL_final}";
		$wpdb->query( $sql );
	}


	public static
	function checkEditRoomConts( $externals, $roomContList, $branchList, $roomList, $roomContID )
	# check the room contianer to make sure everything is filled out
	# there are no dulicate names in the same branch
	# and the rooms are valid
	{
		$error = array();
		$final = NULL;
		# check name is filled and isn't duped in the same branch
		# check for empty room name
		if ( empty( $externals[ 'roomContDesc' ] ) ) {
			$error[] = 'You must enter a room container name.';
		}

		# check dupe name FOR THAT CONTAINER - first, are there any containers?
		if ( !empty( $roomContList[ 'names' ][ $externals[ 'branchID' ] ] ) ) {
			if ( bookaroom_settings::dupeCheck( $roomContList[ 'names' ][ $externals[ 'branchID' ] ], $externals[ 'roomContDesc' ], $externals[ 'roomContID' ] ) == 1 ) {

				$error[] = __( 'That room container name is already in use at that branch. Please choose another.', 'book-a-room' );
			}
		}
		# clean out bad IDs for rooms and check to see if any are selected
		$roomError = FALSE;

		if ( empty( $externals[ 'room' ] ) or!is_array( $externals[ 'room' ] ) ) {
			$roomError = TRUE;
		} else {

			$selectedRooms = array_intersect( array_keys( $roomList[ 'room' ][ $externals[ 'branchID' ] ] ), $externals[ 'room' ] );
			if ( count( $selectedRooms ) == 0 ) {
				$roomError = TRUE;
			}
		}

		if ( $roomError ) {
			$error[] = __( 'You must select at least one room to be in the container.', 'book-a-room' ); 
		}

		# occupancy
		if ( empty( $externals[ 'occupancy' ] ) ) {
			$error[] = __( 'You must enter a maximum occupancy value.', 'book-a-room' );
		} elseif ( !is_numeric( $externals[ 'occupancy' ] ) ) {
			$error[] = __( 'You must enter a valid number for the maximum occupancy.', 'book-a-room' );
		} elseif ( ( float )$externals[ 'occupancy' ] !== ( float )intval( $externals[ 'occupancy' ] ) ) {
			$error[] = __( 'Really? Your maximum occupany can allow a frational human? Make it an integer, please.', 'book-a-room' );
		}

		# if errors, implode and return error messages

		if ( count( $error ) !== 0 ) {
			$final = implode( "<br />", $error );
		}

		return $final;
	}

	public static
	function deleteRoomCont( $roomContID )
	# delete room container
	{
		global $wpdb;

		$table_name = $wpdb->prefix . "bookaroom_roomConts";

		$sql = "DELETE FROM `{$table_name}` WHERE `roomCont_ID` = '{$roomContID}' LIMIT 1";
		$wpdb->query( $sql );
	}

	public static
	function editRoomCont( $externals, $roomList )
	# change the room container settings
	{
		global $wpdb;

		$table_name = $wpdb->prefix . "bookaroom_roomConts";
		$table_name_members = $wpdb->prefix . "bookaroom_roomConts_members";

		$roomContID = $externals[ 'roomContID' ];

		# make amenity list
		# if no amenities, then null
		if ( empty( $externals[ 'room' ] ) ) {
			$roomArr = NULL;
		} else {
			# If there are some, only use valid amenity ids and serialize
			$goodArr = array_keys( $roomList[ 'id' ] );
			$roomArr = array_intersect( $goodArr, $externals[ 'room' ] );
		}
		$isPublic = ( !empty( $externals[ 'isPublic' ] ) ) ? 1 : 0;
		$hideDaily = ( !empty( $externals[ 'hideDaily' ] ) ) ? 1 : 0;

		$sql = "UPDATE `{$table_name}` SET `roomCont_desc` = '{$externals['roomContDesc']}', `roomCont_branch` = '{$externals['branchID']}', `roomCont_isPublic` = '{$isPublic}', `roomCont_hideDaily` = '{$hideDaily}',`roomCont_occ` = '{$externals['occupancy']}' WHERE `roomCont_ID` = '{$roomContID}'";

		$wpdb->query( $sql );

		foreach ( $roomArr as $val ) {
			$roomArrSQL[] = "( '{$roomContID}', '{$val}' )";
		}
		$roomSQL_final = implode( ", ", $roomArrSQL );

		$sql = "DELETE FROM `{$table_name_members}` WHERE `rcm_roomContID` = '{$roomContID}'";
		$wpdb->query( $sql );


		$sql = "INSERT INTO `{$table_name_members}` ( `rcm_roomContID`, `rcm_roomID` ) VALUES {$roomSQL_final}";
		$wpdb->query( $sql );
	}

	public static
	function getExternalsRoomCont()
	# Pull in POST and GET values
	{
		$final = array();

		# setup GET variables
		$getArr = array( 'roomContID' => FILTER_SANITIZE_STRING,
			'branchID' => FILTER_SANITIZE_STRING,
			'action' => FILTER_SANITIZE_STRING );
		# pull in and apply to final
		if ( $getTemp = filter_input_array( INPUT_GET, $getArr ) ) {
			$final += $getTemp;
		}
		# setup POST variables
		$postArr = array( 'action' => FILTER_SANITIZE_STRING,
			'roomContID' => FILTER_SANITIZE_STRING,
			'branchID' => FILTER_SANITIZE_STRING,
			'isPublic' => FILTER_SANITIZE_STRING,
			'hideDaily' => FILTER_SANITIZE_STRING,
			'occupancy' => FILTER_SANITIZE_STRING,
			'roomContDesc' => FILTER_SANITIZE_STRING,
			'room' => array( 'filter' => FILTER_SANITIZE_STRING,
				'flags' => FILTER_REQUIRE_ARRAY ) );



		# pull in and apply to final
		if ( $postTemp = filter_input_array( INPUT_POST, $postArr ) ) {
			$final += $postTemp;
		}
		$arrayCheck = array_unique( array_merge( array_keys( $getArr ), array_keys( $postArr ) ) );

		foreach ( $arrayCheck as $key ) {
			if ( empty( $final[ $key ] ) ) {
				$final[ $key ] = NULL;
			} elseif ( is_array( $final[ $key ] ) ) {
				$final[ $key ] = array_keys( $final[ $key ] );
			} else {
				$final[ $key ] = trim( $final[ $key ] );
			}
		}

		return $final;
	}

	public static
	function getRoomContInfo( $roomContID )
	# get information about room container from database based on the ID
	{
		global $wpdb;

		$table_name = $wpdb->prefix . "bookaroom_roomConts";
		$table_name_members = $wpdb->prefix . "bookaroom_roomConts_members";
		$sql = "SELECT `roomCont`.`roomCont_ID`, `roomCont`.`roomCont_desc`, `roomCont`.`roomCont_branch`, `roomCont`.`roomCont_occ`, `roomCont`.`roomCont_isPublic`, `roomCont`.`roomCont_hideDaily`, 
				GROUP_CONCAT( `members`.`rcm_roomID` ) as `roomCont_roomArr` 
				FROM `$table_name` as `roomCont` 
				LEFT JOIN `$table_name_members` as `members` ON `roomCont`.`roomCont_ID` = `members`.`rcm_roomContID` 
				WHERE `roomCont`.`roomCont_ID` = '{$roomContID}'
				GROUP BY `roomCont`.`roomCont_ID`";

		$final = $wpdb->get_row( $sql, ARRAY_A );

		$roomContInfo = array( 'roomContID' => $roomContID, 'roomContDesc' => $final[ 'roomCont_desc' ], 'branchID' => $final[ 'roomCont_branch' ], 'room' => explode( ',', $final[ 'roomCont_roomArr' ] ), 'occupancy' => $final[ 'roomCont_occ' ], 'isPublic' => $final[ 'roomCont_isPublic' ], 'hideDaily' => $final[ 'roomCont_hideDaily' ] );
		return $roomContInfo;
	}


	public static
	function getRoomContList( $isPublic = false )
	# get a list of room containers
	{
		global $wpdb;
		$roomContList = array();

		$table_name = $wpdb->prefix . "bookaroom_roomConts";
		$table_name_members = $wpdb->prefix . "bookaroom_roomConts_members";

		$where = NULL;

		if ( $isPublic == true ) {
			$where = "WHERE `roomCont`.`roomCont_isPublic` = '1'";
		}

		$sql = "SELECT `roomCont`.`roomCont_ID`, `roomCont`.`roomCont_desc`, `roomCont`.`roomCont_branch`, `roomCont`.`roomCont_occ`, `roomCont`.`roomCont_isPublic`, 
				`roomCont`.`roomCont_hideDaily`, 
				GROUP_CONCAT( `members`.`rcm_roomID` ) as `roomCont_roomArr` 
				FROM `$table_name` as `roomCont` 
				LEFT JOIN `$table_name_members` as `members` ON `roomCont`.`roomCont_ID` = `members`.`rcm_roomContID` 
				{$where}
				GROUP BY `roomCont`.`roomCont_ID` 
				ORDER BY `roomCont`.`roomCont_branch`, `roomCont`.`roomCont_desc`";


		$count = 0;
		$cooked = $wpdb->get_results( $sql, ARRAY_A );

		if ( count( $cooked ) == 0 ) {
			return array( 'id' => array(), 'names' => array(), 'branch' => array() );
		}
		foreach ( $cooked as $key => $val ) {
			# check for rooms
			$roomsGood = ( empty( $val[ 'roomCont_roomArr' ] ) ) ? NULL : explode( ',', $val[ 'roomCont_roomArr' ] );
			$roomContList[ 'id' ][ $val[ 'roomCont_ID' ] ] = array( 'branchID' => $val[ 'roomCont_branch' ], 'rooms' => $roomsGood, 'desc' => $val[ 'roomCont_desc' ], 'occupancy' => $val[ 'roomCont_occ' ], 'isPublic' => $val[ 'roomCont_isPublic' ], 'hideDaily' => $val[ 'roomCont_hideDaily' ] );
			$roomContList[ 'names' ][ $val[ 'roomCont_branch' ] ][ $val[ 'roomCont_ID' ] ] = $val[ 'roomCont_desc' ];
			$roomContList[ 'branch' ][ $val[ 'roomCont_branch' ] ][] = $val[ 'roomCont_ID' ];

		}

		return $roomContList;
	}

	public static
	function showRoomContDelete( $roomContID, $roomContList, $roomList, $branchList )
	# show delete page
	{
		require( BOOKAROOM_PATH . 'templates/roomConts/delete.php' );
	}

	public static
	function showRoomContEdit( $roomContInfo, $branchID, $roomContList, $roomList, $branchList, $amenityList, $action, $actionName )
	# show edit page and fill with values
	{		
		if( empty( $roomContInfo ) ) {
		 $roomContInfo = [ 'occupancy' => null, 'isPublic' => null, 'hideDaily' => null, 'errors' => null, 'roomContID' => null, 'roomContDesc' => null ];
		}
		require( BOOKAROOM_PATH . 'templates/roomConts/edit.php' );
	}

	public static
	function showRoomContList( $roomContList, $roomList, $branchList, $amenityList )
	# show a list of rooms with edit and delete links, or, if none 
	# a message stating there are no branches
	{
		require( BOOKAROOM_PATH . 'templates/roomConts/mainAdmin.php' );
	}
}
?>