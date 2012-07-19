<?php foreach( (array) hm_get_spotify_recent_tracks( 4 ) as $track ) : ?>

	<?php if ( $track['nowplaying'] ) { ?>

		<div class="lastfm-wrap">

			<div class="lastfm-text">
				<?php echo $track['user']; ?> is listening to <strong><?php echo $track['name']; ?></strong> by <?php echo $track['artist']; ?>
			</div>

		</div>

	<?php } else { ?>

		<div class="lastfm-wrap">

			<div class="lastfm-text">
				<?php echo $track['user']; ?> listened to <a href="<?php echo $track['url']; ?>" target="_blank" rel="nofollow"><strong><?php echo $track['name']; ?></strong></a> by <?php echo $track['artist']; ?> <em><?php echo human_time_diff( $track['time'], current_time( 'timestamp' ) ); ?> ago</em>
			</div>

		</div>

	<?php }

<?php endif; ?>