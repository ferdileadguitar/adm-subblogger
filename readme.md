### ![Keepo Logo](https://keepo.me/favicon.ico) Keepo New Status
#### All Status
	- `posts`.`status`
		- Public 
			a. Unpublish (-2) -> Moderated
			b. Draft (-1) -> Has Removed
			c. Publish (1) -> Approved

		- Rejected
			a. Rejected (0)

		- Private 
			a. Private (2)


#### Sticky 
	- `posts`.`sticky` 
		a. Active (1)
		b. Deactivate (0)

#### Premium 
	- `posts`.`premium`
		a. Active (1)
		b. Deactivate (0)

#### Delete (*note : This is not truly deleted data from Posts table it just set status to -99) 
	- Set Delete
		a. Set (-99) -> One Way

#### Up Contents 
	- Desc : New feature request by Admin Keepo to change created post(s) 
			 but also set `is_up_contents` to (1) and it only execute once, So.. , Carefull about this
	- `posts`
		a. Active (1) -> One Way


### ![Keepo Logo](https://keepo.me/favicon.ico) Keepo Admin Routes

| Method         | URI                          | Action                                                                 | Middleware        |
|:--------------:| ----------------------------:| ----------------------------------------------------------------------:| -----------------:|
| `GET|HEAD`     | contents                     | App\Http\Controllers\Pages\ContentController@index                     | web,admin         |
| `GET|HEAD`     | jwt/get/token                | App\Http\Controllers\JWTTokenController@getToken                       | web               |
| `POST`         | login                        | App\Http\Controllers\Auth\LoginController@tryLogin                     | web,admin.promise |
| `GET|HEAD`     | login                        | App\Http\Controllers\Auth\LoginController@showLoginForm                | web,admin.promise |
| `POST`         | logout                       | App\Http\Controllers\Auth\LoginController@logout                       | web,admin.promise |
| `GET|HEAD`     | logout                       | App\Http\Controllers\Auth\LoginController@logout                       | web,admin.promise |
| `POST`         | password/email               | App\Http\Controllers\Auth\ForgotPasswordController@sendResetLinkEmail  | web,guest         |
| `POST`         | password/reset               | App\Http\Controllers\Auth\ResetPasswordController@reset                | web,guest         |
| `GET|HEAD`     | password/reset               | App\Http\Controllers\Auth\ForgotPasswordController@showLinkRequestForm | web,guest         |
| `GET|HEAD`     | password/reset/{token}       | App\Http\Controllers\Auth\ResetPasswordController@showResetForm        | web,guest         |
| `POST`         | register                     | App\Http\Controllers\Auth\RegisterController@register                  | web,guest         |
| `GET|HEAD`     | register                     | App\Http\Controllers\Auth\RegisterController@showRegistrationForm      | web,guest         |
| `GET|HEAD`     | /                            | App\Http\Controllers\Pages\ContentController@index                     | web,admin         |

API REQUEST                           
| Method         | URI                          | Action                                                                 | Middleware        |
|:--------------:| ----------------------------:| ----------------------------------------------------------------------:| -----------------:|
| `POST`         | api/asset/cover-img          | App\Http\Controllers\Api\AssetController@postImageCover                | Api               |
| `GET|HEAD`     | api/contents                 | App\Http\Controllers\Api\ContentController@getContents                 | api               |
| `DELETE`       | api/contents                 | App\Http\Controllers\Api\ContentController@deleteContent               | api               |
| `GET|HEAD`     | api/contents/count-moderated | App\Http\Controllers\Api\ContentController@getCountModerated           | api               |
| `PUT`          | api/contents/set-premium     | App\Http\Controllers\Api\ContentController@setPremium                  | api               |
| `PUT`          | api/contents/set-status      | App\Http\Controllers\Api\ContentController@setStatus                   | api               |
| `PUT`          | api/contents/set-sticky      | App\Http\Controllers\Api\ContentController@setSticky                   | api               |
| `PUT|GET|HEAD` | api/feeds/{type?}            | App\Http\Controllers\Api\ContentController@feedState                   | api               |
| `GET|HEAD`     | api/tags                     | App\Http\Controllers\Api\ContentController@getTags                     | api               |

