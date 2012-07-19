<?php

require( 'tlc-transients.php' );

/**
 * Add Lastfmn username field to the profile.
 */
function hm_lastfm_user_field( $contactmethods ) {
    $contactmethods['lastfm_username'] = 'Last FM Username';
    return $contactmethods;
}
add_filter( 'user_contactmethods', 'hm_lastfm_user_field' );

/**
 * Grab a list of recently played tracks from the LAST FM API
 */
function hm_update_spotify() {

    foreach ( get_users( array( 'meta_key' => 'lastfm_username' ) ) as $user ) {

        $lastfm_user = get_the_author_meta( 'lastfm_username', $user->ID ) ;

        if ( empty($lastfm_user))
            continue;

        $api_response = wp_remote_get( 'http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user=' . $lastfm_user . '&api_key=4c3c4488d4ce8c7047e810594dc6009b' );

        if ( is_wp_error( $api_response ) || $api_response['response']['code'] == '404' )
            return false;

        $spotify_response[get_the_author_meta( 'first_name', $user->ID )] = wp_remote_retrieve_body( $api_response );

    }

	if( empty( $spotify_response ) )
		return array();

    return $spotify_response;


}

/**
 * Get an array or recently played Spotify tracks
 */
function hm_get_spotify_recent_tracks( $limit ) {

    // Cache the spotify data for three minutes
    $spotify_responses = tlc_transient( 'hm_spotify' )
        ->updates_with( 'hm_update_spotify' )
        ->expires_in( 180 )
        ->background_only()
        ->get();

    if ( empty( $spotify_responses ) )
        return false;

    $playlists = array();

    foreach ( $spotify_responses as $key => $spotify_response )
        $playlists[$key] = new SimpleXMLElement( $spotify_response );

    foreach( $playlists as $user => $playlist ) {

        foreach ( $playlist->recenttracks->track as $track ) {

            $attribute = $track->attributes();

            if ( ! empty( $attribute['nowplaying'] ) ) {

                $time = time();

                while( isset( $tracks[$time] ) )
                    $time++;

            } else {

                $time = $track->date->attributes();
                $time = (int) $time['uts'];

            }

            $track->user = $user;

            $tracks[$time] = $track;

        }
    }

    $tracks = json_decode( json_encode( $tracks ), true );

    foreach( $tracks as $key => &$track ) {

        $track['time'] = $key;
        $track['nowplaying'] = isset( $track['@attributes']['nowplaying'] ) ? true : false;
        unset( $track['mbid'] );
        unset( $track['@attributes'] );

        }

    // Sort the tracks chronologically
    ksort( $tracks );

    return array_slice( array_reverse( $tracks, true ), 0, $limit, true );

}