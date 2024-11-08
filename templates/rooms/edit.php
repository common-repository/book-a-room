<link href="<?php echo plugins_url(); ?>/book-a-room/css/bookaroom_meetings.css" rel="stylesheet" type="text/css"/>
<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2>
		<?php _e( 'Book a Room Administration - Rooms', 'book-a-room' ); ?>
	</h2>
</div>
<h2>
	<?php
	switch ( $action ) {
		case 'addCheck':
		case 'add':
			_e( 'Add a Room', 'book-a-room' );
			break;
		case 'editCheck':
		case 'edit':
			_e( 'Edit a Room', 'book-a-room' );
			break;
		default:
			wp_die( "ERROR: BAD ACTION on room add/edit: " . $action );
			break;
	}
	?>
</h2>
<?php 
# Display Errors if there are any
if ( !empty( $roomInfo['errors'] ) ) {
 ?>
<h3 style="color:red;"><strong><?php echo $roomInfo['errors']; ?></strong></h3>
<?php 
}
?>
<form name="form1" method="post" action="?page=bookaroom_Settings_Rooms&action=<?php echo $action; ?>&roomID=<?php echo ( isset( $roomInfo[ 'roomID' ] ) ) ? $roomInfo[ 'roomID' ] : null; ?>">
	<table class="tableMain">
		<tr>
			<td colspan="2"><?php _e( 'Room Information', 'book-a-room' ); ?></td>
		</tr>
		<tr>
			<td><label for="roomDesc"><?php _e( 'Room Name', 'book-a-room' ); ?></label>
			</td>
			<td>
				<input name="roomDesc" type="text" id="roomDesc" value="<?php echo ( isset( $roomInfo[ 'roomDesc' ] ) ) ? $roomInfo[ 'roomDesc' ] : null; ?>" size="48" maxlength="64">
			</td>
		</tr>
		<tr>
			<td><label for="branch"><?php _e( 'Branch', 'book-a-room' ); ?></label>
			</td>
			<td>
				<select name="branch" id="branch">
					<?php
					$checked = ( empty( $roomInfo[ 'branch' ] ) ) ? ' checked="checked"' : NULL;
					
					?>
					<option value=""<?php echo $checked; ?>><?php _e( 'Choose branch', 'book-a-room' ); ?></option>
					<?php
					foreach ( $branchList as $key => $val ) {
						#$temp = $branch_line;
						$checked = ( !empty( $roomInfo[ 'branch' ] ) and $roomInfo[ 'branch' ] == $key ) ? ' selected="selected"' : NULL;
					?>
					<option value="<?php echo $key; ?>"<?php echo $checked; ?>><?php echo $val; ?></option>
					<?php
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="subHeader"><?php _e( 'Amenities', 'book-a-room' ); ?></td>
		</tr>
		<?php
		if ( empty( $amenityList ) or !is_array( $amenityList ) or count( $amenityList ) == 0 ) {
		?>
		<tr>
			<td colspan="2"><?php _e( 'You haven\'t created any amenities.', 'book-a-room' ); ?></td>
		</tr>
		<?php
		} else  {
			# if there are amenities, list them and check any that are in the room settings
			foreach ( $amenityList as $a_key => $a_val ) {
				$checked = ( !empty( $roomInfo[ 'amenity' ] ) and is_array( $roomInfo[ 'amenity' ] ) and in_array( $a_key, $roomInfo[ 'amenity' ] ) ) ? ' checked="checked"' : NULL;
		?>
		<tr>
			<td><label for="amenity[<?php echo $a_key; ?>]"><?php echo $a_val; ?></label>
			</td>
			<td>
				<input name="amenity[<?php echo $a_key; ?>]" type="checkbox" id="amenity[<?php echo $a_key; ?>]"<?php echo $checked; ?>>
			</td>
		</tr>
		<?php
			}
		}

		?>
		<tr>
			<td colspan="2"><input type="submit" name="button" id="button" value="<?php _e( 'Submit', 'book-a-room' ); ?>">
			</td>
		</tr>

	</table>
</form>