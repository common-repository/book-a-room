<link href="<?php echo plugins_url(); ?>/book-a-room/css/bookaroom_meetings.css" rel="stylesheet" type="text/css"/>
<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2>
		<?php _e( 'Book a Room Administration - Rooms', 'book-a-room' ); ?>
	</h2>
</div>
<h2>
	<?php _e( 'Current Room Containers by Branch', 'book-a-room' ); ?>
</h2>
<?php
if ( is_null( $branchList ) or!is_array( $branchList ) or count( $branchList ) == 0 ) {
	?>
<p><a href="#"> </a>
	<?php _e( 'You haven\'t created any branches yet so you can\'t create any rooms.', 'book-a-room' ); ?>
</p>
<p>
	<a href="?page=bookaroom_Settings_Branches&amp;action=add">
		<?php _e( 'Create a new branch.', 'book-a-room' ); ?>
	</a>
</p>
<?php
} else {
	?>
	<p>
		<a href="?page=bookaroom_Settings_Rooms&action=add">
			<?php _e( 'Create a new room.', 'book-a-room' ); ?>
		</a>
	</p>
	<?php
}
if ( is_null( $roomList ) or!is_array( $roomList ) or count( $roomList ) == 0 ) {
	?>
	<p><?php _e( 'You haven\'t created any rooms.', 'book-a-room' ); ?></p>
	<?php
} else {
	?>
	<table class="tableMain">
		<tr>
			<td colspan="2"><?php _e( 'Branch List', 'book-a-room' ); ?></td>
		</tr>
		<?php
		foreach ( $branchList as $b_key => $b_val ) {
			?>
		<tr>
			<td colspan="2" class="subHeader"><?php echo $b_val; ?></td>
		</tr>
		<?php
			if ( empty( $roomList[ 'room' ][ $b_key ] ) ) {
			?>
		<tr>
			<td colspan="2"><?php _e( 'No rooms in this branch.', 'book-a-room' ); ?>
			</td>
		</tr>
		<?php
			} else {
				foreach ( $roomList[ 'room' ][ $b_key ] as $r_key => $r_val ) {
					if ( empty( $roomList[ 'id' ][ $r_key ][ 'amenity' ] ) or count( $roomList[ 'id' ][ $r_key ][ 'amenity' ] ) == 0 ){
						$amenityListArr = __( 'None', 'book-a-room' ); 
					} else {
						$a_list = array();
						foreach ( $roomList[ 'id' ][ $r_key ][ 'amenity' ] as $a_key => $a_val ) {
							if( !empty( $amenityList[ $a_val ] ) ) {
								$a_list[] = $amenityList[ $a_val ];
							}
						}
						$amenityListArr = implode( ', ', $a_list );
					}
				?>
		<tr>
			<td class="bufferLeft"><strong><?php echo $r_val; ?></strong>
			</td>
			<td><a href="?page=bookaroom_Settings_Rooms&branchID=<?php echo $b_key; ?>&roomID=<?php echo $r_key; ?>&action=edit"><?php _e( 'Edit', 'book-a-room' ); ?></a> | <a href="?page=bookaroom_Settings_Rooms&branchID=<?php echo $b_key; ?>&roomID=<?php echo $r_key; ?>&action=delete"><?php _e( 'Delete', 'book-a-room' ); ?></a>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="bufferLeftDouble"><em><?php _e( 'Amenities', 'book-a-room' ); ?>: <?php echo $amenityListArr; ?></em>
			</td>
		</tr>
		<?php
				}
			}
		}
		?>
	</table>
	<?php
}
?>