<?php

// All In One helpers, contain not specific helpers

// Addon
use App\PostShare;
use App\Thumbnail;
use App\User;
use \Eventviva\ImageResize;

/**
 * Generate image url
 * @param string $filename
 * @return string
 */
if ( ! function_exists('img_url'))
{
	function img_url($filename = false, $height = 0, $width = 0)
	{
		if($height > 0 && $width > 0)
			$filename = "{$height}x{$width}/$filename";

		return isset($filename) ? url(config('app.imgurl').'/'.$filename) : false;
	}
}

/**
 * Generate clean protocol url
 * @param string $filename
 * @return string
 */
if ( ! function_exists('https_url'))
{
	function https_url($url = false)
	{
		$filename = substr($url, 0, 1) == '/' ? $url : '/'.$url;
		$filename = $url == '/' ? url('/') : str_replace(['//www.m.keepo','//keepo.me/index.php', '//keepo.me.me'], ['//keepo.me','//keepo.me', '//keepo.me'], url('/')).$filename;
		$filename = isset($filename) ? preg_replace('/http(s?):/', '',$filename) : false;
		return $filename;
	}
}

/**
 * Image thumbnails generator
 * @param string $path
 * @param string $filename
 * @param string $ext
 * @param integer $objID
 * @param object|bool $ftp_conn
 * @return array
 */
if( ! function_exists('genThumbnails'))
{
	function genThumbnails($path, $filename, $ext, $objID, $ftp_conn = false, $getFromServer = false)
	{
        // Resize based on config's available size
        $thumbnails   = config('feeds.thumbnails_size');
        $toReturn     = array('isUploaded' => true, 'uploadedFile' => array());

        if(! $ftp_conn)
        {
            // Lets first, setup for FTP
            $ftp_conn  = @ftp_connect(config('app.imgftp.host'), config('app.imgftp.port'));
            $ftp_login = @ftp_login($ftp_conn, config('app.imgftp.username'), config('app.imgftp.password'));

            // Passive mode?
            @ftp_pasv($ftp_conn, config('app.imgftp.passive'));
        }

        // Iterate based on desired size
        foreach ($thumbnails as $key => $row)
        {
        	// What size and check if exists
            $beSize = "{$row[0]}x{$row[1]}";
        	if(Thumbnail::where('object_file_id', '=', $objID)->where('size', '=', $beSize)->count() == 0)
        	{
        		// Local file name
	            $resizedName        = str_replace($ext, "_{$beSize}".$ext, $filename); // temporary name for local
	            $resizedFullPath    = $path.'/'.$resizedName; // Path of local file

	            if($getFromServer && ! @ftp_get($ftp_conn, $resizedFullPath, config('app.imgftp.dir').'/'.$filename, FTP_BINARY))
	            	continue;

	            // Lets make thumbnail
	            $image              = new ImageResize($path.'/'.$filename); // Init resizer
	            $image->quality_jpg = 100; // By default is : 75
	            $image->resizeToBestFit($row[0], $row[1]); // Resize to best fit, height x width
	            $image->save($path.'/'.$resizedName); // Save thumbnail to local

	            // Cause no is_dir, lets just make dir
	            @ftp_mkdir($ftp_conn, config('app.imgftp.dir').'/'.$beSize);
	            @ftp_chmod($ftp_conn, 0777, config('app.imgftp.dir').'/'.$beSize);

	            // Lets move it
	            if(@ftp_put($ftp_conn, config('app.imgftp.dir')."/{$beSize}/{$filename}", $resizedFullPath, FTP_BINARY))
	            {
	                // Set flag and remove local file
	                $toReturn['uploadedFile'][] = $beSize.'/'.$filename; // Path on server
	                unlink($resizedFullPath); // Tumbnail

	                // Set to database
	                Thumbnail::create(array('object_file_id' => $objID, 'size' => $beSize));
	            }
	            else
	            {
	            	$toReturn['isUploaded'] = false;
	                break;
	            }
        	}
        }

        return $toReturn;
	}
}
/**
 * Generate API URL
 * @param string $addon
 * @return string
 */
if ( ! function_exists('api_url'))
{
	function api_url($addon = false)
	{
		return url(config('api.api_url').'/'.$addon);
	}
}

/**
 * API Parameter
 * @param array $body
 * @return array
 */
if ( ! function_exists('api_param'))
{
	function api_param($body = array())
	{
		return array_merge(array(
			'access_token' => Session::get('token')
		), $body);
	}
}

/**
 * Encrypt password for user
 * @param string $salt
 * @param string $password
 * @return string
 */
if ( ! function_exists('cryptPass'))
{
	function cryptPass($salt, $password)
	{
		return sha1($salt . $password);
	}
}

/**
 * Create salt for user password
 * @return string
 */
if ( ! function_exists('createSalt'))
{
	function createSalt()
	{
		return md5(uniqid(rand() . strtotime(date('Y-m-d H:i:s') . rand()), true));
	}
}


/**
 * Round image size to Kilobyte
 * @param integer $size
 * @param bool $isObj
 * @return float
 */
if ( ! function_exists('roundImageSize'))
{
	function roundImageSize($fileObj, $isObj = true)
	{
		// Is this symfony's file object? If no, just round it
		if($isObj)
			return round(($fileObj->getClientSize() / 1000), 2);
		else
			return round(($fileObj / 1000), 2);
	}
}

/**
 * Get remote file mime
 * @param string $url
 * @return string
 */
if ( ! function_exists('remoteMime'))
{
	function remoteMime($url)
	{
	    // Request using mime, cause its faster
	    $ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_exec($ch);

	    return curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
	}
}

/**
 * Check multiple key exists
 * @param array $keys
 * @param array $search
 * @return bool
 */
if ( ! function_exists('array_keys_exists'))
{
	function array_keys_exists($keys, $search)
	{
		foreach ($keys as $key => $row)
		{
			if(! array_key_exists($row, $search))
				return false;
		}
		return true;
	}
}

/**
 * Update user's point
 * @param integer $uid
 * @param typestring $point
 * @return boolean
 */
if ( ! function_exists('updatePoint'))
{
	function updatePoint($uid, $point)
	{
		$user = User::find($uid);

		// Is user and operator exists?
		if($user !== null && (strstr($point, '-') || strstr($point, '+')))
		{
			// Set by string
			$newpoint = $user->points;
			eval('$newpoint = $newpoint '. $point .';');

			// Save
			$user->points = $newpoint;
			$user->save();

			return true;
		}
		else
			return false;
	}
}

/**
 * Get share count of posts from social media
 * @param object|array $posts
 * @return void
 */
if(! function_exists('shareCount'))
{
	function shareCount($posts = array())
	{
		// Changing from non array to array
		if(! is_array($posts))
			$posts = array($posts);

	    if(! empty($posts))
	    {
	    	$socials = array(
		        'fb' => 'http://graph.facebook.com/?id=',
		        'twitter' => 'http://opensharecount.com/count.json?url=',
		    );

		    // Iterate action per posts
		    foreach ($posts as $key => $post)
		    {
		        $share = array();

		        // Iterate action per social media
		        $addonUrl = (strtotime($post->created_on) < 1469984400) ? $post->channel->slug : $post->user->slug; // 2016-08-01 00:00:00
		        $addonUrl = urlencode("http://keepo.me/{$addonUrl}/{$post->slug}");
		        foreach ($socials as $social => $url)
		        {
		            // Take share count
		            $result = Unirest\Request::get($url.$addonUrl, array('Accept' => 'application/json'));

		            // If share is more than before
		            if($result->code == 200)
		            {
		                if($social == 'fb')
		                    $share[$social] = $result->body->share->share_count;
		                else
		                    $share[$social] = $result->body->count;
		            }
		        }

		        // Lets update the count
		        if(@$share['fb'] > 0 || @$share['twitter'] > 0)
		        {
		            // Setup
		            $ps = PostShare::where('post_id', '=', $post->id);
		            $data = array(
		                'post_id'   => $post->id,
		                'fb'        => (isset($share['fb'])) ? $share['fb'] : 0,
		                'twitter'   => (isset($share['twitter'])) ? $share['twitter'] : 0,
		            );
		            $data['shares'] = $data['fb'] + $data['twitter'];

		            // Lets act
		            if($ps->count() == 0)
		                PostShare::create($data);
		            else
		                $ps->update($data);
		        }
		    }
	    }
	}
}
