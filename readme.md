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



