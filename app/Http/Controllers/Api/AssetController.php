<?php

namespace App\Http\Controllers\Api;

use Intervention\Image\Facades\Image;

use Cache;

// Model
use App\UserCover;
use App\ObjectFile;
use App\Thumbnail;

// Addon
use \Eventviva\ImageResize;
use vendor;

class AssetController extends \App\Http\Controllers\ApiController
{

	public function postImageCover() {
		$file    = $this->request->file('file');
		$size    = roundImageSize($file); // Kilobyte and rounded
      	// rules for naming variables = https://softwareengineering.stackexchange.com/questions/149303/naming-classes-methods-functions-and-variables
      	$mime_type = $file->getMimeType();

      	$request_type = $this->request->input('type');
      	$gif_flag = $mime_type == 'image/gif' && $request_type == 'body' ? true : false;
      	$gif_thumbnail_ext = 'jpg';

      	if($request_type == 'ktp'){
        	$image_sizes = ['65x65','35x35','108x108'];
      	}elseif($request_type == 'cover_profile'){
        	$image_sizes = ['920x190'];
      	}elseif($request_type=='body'){
        	$image_resolutions = Image::make($file->getRealpath());
        	list($width_oriented, $height_oriented) = [$image_resolutions->width(), $image_resolutions->height()];
        	$image_sizes = ["{$width_oriented}x{$height_oriented}"];
      	}else{
        	$image_sizes = config('feeds.covers_size');
      	}

     	 // Is this file correctly uploaded?
      	if($file === null && !$file->isValid()):
        	return $this->abortRequest(404, 'not_found', 'Something went wrong, please try again correctly and check your connection');
      	endif;

      	if($size >= config('feeds.img_opt.max_size')):
        	return $this->abortRequest(400, 'bad_request', 'Your image size should not more than '.number_format(config('feeds.img_opt.max_size')).' Kb');
     	endif;

      	$mime_type_check = in_array($mime_type, config('feeds.img_opt.mime'));
      	if(!$mime_type_check): // Using mime guesser based on content, see : http://php.net/manual/en/intro.fileinfo.php
        	return $this->abortRequest(400, 'bad_request', 'Your image\'s mime is not allowed');
      	endif;

      	$arr_files = [];
      	$image_save_path = realpath('./../temp/asset');
      	$time_stamp = date('YmdHis');
      	$ftp_temp_path = $image_save_path.'/images/';
      	$image_path = $ftp_temp_path.$time_stamp;
      	foreach ($image_sizes as $key => $value):
        	$ext     = '.'.$file->getClientOriginalExtension();
        	$arr_size = explode('x',$value);
        	$width = intval($arr_size[0]);
        	$height = intval($arr_size[1]);

        	// create path if there is no exists
        	if (!is_dir($image_path)):
          	\File::makeDirectory($image_path, 0777, true);
    			endif;

        	// resizing proses from temp file, save it and inset watermark on it
        	$image_make = Image::make($file->getRealpath());
        	if($request_type == 'ktp'){
          		$image_make = $image_make->fit($width,$height);
        	}elseif($request_type == 'body' && !$gif_flag){
          		$landscape_orientation = $width > $height ? true : false;
          	if($landscape_orientation && ($width > 728)){
            	$image_make = $image_make->resize($width=728, null, function($constraint){
              		$constraint->aspectRatio();
            	});
            	list($width, $height) = [$image_make->width(), $image_make->height()]; // resetting new width & height variable after resizing
            	$value = "{$width}x{$height}";
          	}else{
            	$image_make = $image_make->fit($width, $height);
          	}
       	 	}else{
          		$image_make = $image_make->fit($width, $height, null, 'top');
        	}

        	if(!$gif_flag):
        	$image_make = (string) $image_make->encode('data-url',70); // encode file and set quality to 70%
       	 	$image_make = str_replace("data:{$mime_type};base64,", '', $image_make);
        	$image_make = base64_decode($image_make); // decoding image file as imageable
        	else:
          	$image_make = $image_make->encode($gif_thumbnail_ext,70);
        	endif;

        	$image_file_name = $value.'--'.str_replace(' ','-', strtolower(basename($file->getClientOriginalName(),$ext)));
        	$saving_image_path_ext = $image_path.'/'.$image_file_name.$ext;
        	// $image_make        = $gif_flag ? $file->move( $image_path, $image_file_name.$ext ) : file_put_contents($saving_image_path_ext, $image_make); // lets save quality image size to temporary path
        	if($gif_flag){
          	// make 2 files separated on file extension for click and play
          	if($image_make->save($image_path.'/'.$image_file_name.'.'.$gif_thumbnail_ext)){
            	$file->move( $image_path, $image_file_name.$ext );
          		}
        	}else{
          		$image_make = file_put_contents($saving_image_path_ext, $image_make);
        	}
        	if($image_make){
          		$arr_files[$key] = [
                                    'full_path'=> $saving_image_path_ext,
                                    'file_type'=> $mime_type,
                                    'file_path'=> $time_stamp.'/'.$image_file_name.$ext,
                                    'file_name'=> $image_file_name.$ext,
                                    'raw_name'       => $image_file_name,
                                    'orig_name'      => $image_file_name.$ext,
                                    'client_name'    => $image_file_name.$ext,
                                    'file_ext'       => $ext,
                                    'file_size'      => $size,
                                    'is_image'       => 1, // Of course its image! :D
                                    'image_width'    => $width,
                                    'image_height'   => $height,
                                    'image_type'     => str_replace('image/', false, $mime_type),
                                    'image_size_str' => 'width="'. $width .'" height="'. $height .'"'
                                  ];
        }
      	endforeach;

      	$message = '';
      	// make sure there is no image resizing was completed
      	if(count($arr_files) != count($image_sizes)):
        	$rm_dir = $this->recursiveRemoveDirectory($image_path);
       	 	if(!$rm_dir){
          		$message = 'Remove Path failed!';
        	}
        	$return = $this->abortRequest(404, 'not_found', 'Something went wrong, '.$message.' please try again! (1)');
        	return $return;
      	endif;

      	// Lets save to ObjectFile for first array files image
      	$main_image_file = reset($arr_files);
      	if($object_file = (new ObjectFile)->create($main_image_file)){
        // Just in case
        $is_uploaded   = true;

        // Lets first, setup for FTP
        $ftp_conn  = @ftp_connect(config('app.imgftp.host'), config('app.imgftp.port'));
        $ftp_login = @ftp_login($ftp_conn, config('app.imgftp.username'), config('app.imgftp.password'));

        // Passive mode?
        @ftp_pasv($ftp_conn, config('app.imgftp.passive'));

        // Cause no is_dir, lets just make dir
        @ftp_mkdir($ftp_conn, config('app.imgftp.dir').'/'.$time_stamp);
        @ftp_chmod($ftp_conn, 0777, config('app.imgftp.dir').'/'.$time_stamp);

        // Check connection and upload main image
        $file_name_ftp = $main_image_file['file_name'];
        if(! empty($ftp_conn) && ! empty($ftp_login) && @ftp_put($ftp_conn, config('app.imgftp.dir')."/{$time_stamp}/{$file_name_ftp}", $main_image_file['full_path'], FTP_BINARY))
        {
            // upload png file convert result from image make with only gif type
            if($gif_flag) @ftp_put($ftp_conn, str_replace('.gif', '.'.$gif_thumbnail_ext, config('app.imgftp.dir')."/{$time_stamp}/{$file_name_ftp}"), str_replace('.gif', '.'.$gif_thumbnail_ext, $main_image_file['full_path']), FTP_BINARY);

            if($request_type != 'body'):
            foreach ($arr_files as $f => $file) {
              if(@ftp_put($ftp_conn, config('app.imgftp.dir')."/{$time_stamp}/{$file['file_name']}", $file['full_path'], FTP_BINARY)){
                $thumbnail = (new Thumbnail)->create(['object_file_id'=> $object_file->id, 'size'=> $file['image_width'].'x'.$file['image_height']]);
              }
            }
            endif;
            // Set flag
            $is_uploaded = true;
        }
        else
            $is_uploaded = false;

        if($is_uploaded){
          	$object_file->update(array(
              'file_path'    => config('app.imgurl').'/'.$time_stamp,
              'full_path'    => config('app.imgurl').'/'.$main_image_file['file_path'],
          	));

          // lets create user cover
          if($request_type == 'cover_profile'){
            $cover_profile_params = ['user_id'=>@$this->userInfo->id,'object_file_id'=> @$object_file->id];
            $cover_user = UserCover::where('user_id','=',@$this->userInfo->id);
            // lets destroy image on ftp if already exists or create record only if not exists
            if($cover_user->count() == 0){
              	$set_cover_user = (new UserCover)->create($cover_profile_params);
            	}else{
	      		// cannot ftp_delete operation failed on server so handle it with update is_image become false, maybe one day we create removing image with this field
              	if($current_cover_image = $cover_user->get()->first()->objectFile->update(['is_image'=> false])):
              	//$replace_host = getenv('APP_ENV') != 'local' ? '//media.keepo.me' : '//localhost';
	      		//sleep(5);
              	//if(ftp_delete($ftp_conn, str_replace($replace_host,'',$current_cover_image))){
              	unset($cover_profile_params['user_id']);
              	$set_cover_user = $cover_user->update($cover_profile_params);
              	endif;
              	//}else{
              	//  return $this->abortRequest(404, 'not_found', 'Something went wrong when removing file');
              	//}
            }
            // lets refresh cache for cover image
            Cache::tags(['copic:'.@$this->userInfo->id,'luid:'.@$this->userInfo->username])->flush();
        }
          	$return = $this->giveJson(['id' => $object_file->id, 'name' => $object_file->file_name, 'url' => $object_file->full_path, 'width' => $object_file->image_width, 'height' => $object_file->image_height]);
        }else{
          	$object_file->delete(); // destroy record objectFile
          	$return = $this->abortRequest(500, 'internal_server_error', 'Something went wrong, please try again (2)');
        }

      }
      $rm_dir = $this->recursiveRemoveDirectory($image_path);
      if(!$rm_dir){
       		$message = 'Remove Path failed!';
        	$return = $this->abortRequest(500, 'internal_server_error', 'Something went wrong, '.$message.' please try again (3)');
      }
      return $return;
	}

	private function recursiveRemoveDirectory($dir)
    {
        foreach(scandir($dir) as $file) {
          if ('.' === $file || '..' === $file) continue;
          if (is_dir("$dir/$file")) $this->recursiveRemoveDirectory("$dir/$file");
          else unlink("$dir/$file");
        }
        if(rmdir($dir)){
          return true;
        }else{
          return false;
        }
    }
    /*
      *http://stackoverflow.com/questions/8773843/php-ftp-put-copy-entire-folder-structure
    */
    private function ftp_putAll($conn_id, $src_dir, $dst_dir) {
      $d = dir($src_dir);
      while($file = $d->read()) { // do this for each file in the directory
          if ($file != "." && $file != "..") { // to prevent an infinite loop
              if (is_dir($src_dir."/".$file)) { // do the following if it is a directory
                  if (!@ftp_chdir($conn_id, $dst_dir."/".$file)) {
                      @ftp_mkdir($conn_id, $dst_dir."/".$file); // create directories that do not yet exist
                  }
                  $this->ftp_putAll($conn_id, $src_dir."/".$file, $dst_dir."/".$file); // recursive part
              } else {
                  @ftp_put($conn_id, $dst_dir."/".$file, $src_dir."/".$file, FTP_BINARY); // put the files
              }
          }
      }
      $d->close();
    }
}	
