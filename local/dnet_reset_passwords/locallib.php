<?php

/**
 * method masks the username of an email address
 *
 * @param string $email the email address to mask
 * @param string $mask_char the character to use to mask with
 * @param int $percent the percent of the username to mask
 */
function mask_email( $email, $mask_char='*', $percent=50 )
{
        list( $user, $domain ) = preg_split("/@/", $email );
        $len = strlen( $user );
        $mask_count = floor( $len * $percent /100 );
        $offset = floor( ( $len - $mask_count ) / 2 );
        $masked = substr( $user, 0, $offset )
                .str_repeat( $mask_char, $mask_count )
                .substr( $user, $mask_count+$offset );
        return( $masked.'@'.$domain );
}
