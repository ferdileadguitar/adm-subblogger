@extends('layouts.app')

@push('css')
	<link rel="stylesheet" type="text/css" href="{{ asset('dist/css/content.css') }}">

	<!-- Bootstrp 4 -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">

	<style type="text/css">
		body {
		  overflow-x: hidden;
		}

		#wrapper {
		  padding-left: 0;
		  -webkit-transition: all 0.5s ease;
		  -moz-transition: all 0.5s ease;
		  -o-transition: all 0.5s ease;
		  transition: all 0.5s ease;
		}

		#wrapper.toggled {
		  padding-left: 250px;
		}

		#sidebar-wrapper {
		  z-index: 1000;
		  position: fixed;
		  left: 250px;
		  top: 60px;
		  width: 250px;
		  height: 100%;
		  margin-left: -250px;
		  overflow-y: auto;
		  background: #212121;
		  -webkit-transition: all 0.5s ease;
		  -moz-transition: all 0.5s ease;
		  -o-transition: all 0.5s ease;
		  transition: all 0.5s ease;
		}

		#wrapper.toggled #sidebar-wrapper {
		  width: 250px;
		}

		#page-content-wrapper {
		  width: 100%;
		  position: absolute;
		  padding: 15px;
		}

		#wrapper.toggled #page-content-wrapper {
		  position: absolute;
		  margin-right: -250px;
		}


		/* Sidebar Styles */

		.sidebar-nav {
		  position: absolute;
		  top: 0;
		  width: 250px;
		  margin: 0;
		  padding: 0;
		  list-style: none;
		}

		.sidebar-nav li {
		  text-indent: 20px;
		  line-height: 40px;
		}

		.sidebar-nav li a {
		  display: block;
		  text-decoration: none;
		  color: #999999;
		  font-size: 18px;
		}

		.sidebar-nav li a:hover {
		  text-decoration: none;
		  color: #fff;
		  background: rgba(255, 255, 255, 0.2);
		}

		.sidebar-nav li a:active, .sidebar-nav li a:focus {
		  text-decoration: none;
		}

		.sidebar-nav>.sidebar-brand {
		  height: 65px;
		  font-size: 18px;
		  line-height: 60px;
		}

		.sidebar-nav>.sidebar-brand a {
		  color: #999999;
		}

		.sidebar-nav>.sidebar-brand a:hover {
		  color: #fff;
		  background: none;
		}

		.content {
			margin-left: 200px;
		}

		@media(min-width:768px) {
		  #wrapper {
		    padding-left: 0;
		  }
		  #wrapper.toggled {
		    padding-left: 250px;
		  }
		  #sidebar-wrapper {
		    width: 250px;
		  }
		  #wrapper.toggled #sidebar-wrapper {
		    width: 250px;
		  }
		  #page-content-wrapper {
		    padding: 20px;
		    position: relative;
		  }
		  #wrapper.toggled #page-content-wrapper {
		    position: relative;
		    margin-right: 0;
		  }

		  /* enable absolute positioning */
		.inner-addon {
		  position: relative;
		}

		/* style glyph */
		.inner-addon .glyphicon {
		  position: absolute;
		  padding: 10px;
		  pointer-events: none;
		}

		/* align glyph */
		.left-addon .glyphicon  { left:  0px;}
		.right-addon .glyphicon { 
			right: 0px;
		    width: 10px;
		    height: 10px;
		    background-color: red;
		    border-radius: 5px;
		    margin: 5px;
		    color: white;
		    cursor: pointer;
		    z-index: 99;
		    display: flex;
		    align-items: center;
		    justify-content: center;
		}

		/* add padding  */
		.left-addon input  { padding-left:  30px; }
		.right-addon input { padding-right: 30px; }
		}
	</style>

@endpush

@section('content')
	<div class="container-fluid">

		<div id="wrapper">
			<!-- Sidebar -->
	        <div id="sidebar-wrapper">
	            <ul class="sidebar-nav">
	                <li class="sidebar-brand">
	                    <a>
	                        Configuration
	                    </a>
	                </li>
	                <li>
	                    <a href="#login">Login Access</a>
	                </li>

	                <li>
	                    <a href="#contributor">Contributor</a>
	                </li>
	            </ul>
	        </div>
	        <!-- /#sidebar-wrapper -->

	        <div class="content col-lg-10">
	        	
	        	<div class="col-lg-12 col-md-8" id="login">
	        		<header>
						<h1>Admin List</h1>

						<h3>Here is written who is entitled to get access to the admin page</h3>
						<h4>*note : make sure it have account on keepo</h4>
						<hr />
	        		</header>
					
					@if(Session::has('error'))
					    <div class="alert alert-danger"> 
					    	{!! Session::get('error') !!}
					    </div>
					@endif

					<form action="{{ route('accessUser') }}" method="POST">
						@if(!empty(@$user))
							@forelse($user as $item)
							  	<div class="form-row">
							    	<div class="form-group col-md-5">
							          		<input type="email" class="form-control col-sm-12" value="{{ $item }}" readonly="true" name="emailList[]" style="font-size: 16px;" />
							    	</div>
							    
							    	<div class="form-group col-md-2">
									  	<a href="{{ url('access/user/rm?key='.$item) }}" class="btn btn-danger"><i class="glyphicon glyphicon-remove" style="color: white;"></i></a>
							    	</div>
							  	</div>
							@empty
							@endforelse

						@else
							<h4><i>kosong</i></h4>
						@endif
							
						<div class="form-row">
					    	<div class="form-group col-md-9">
				          		<input type="email" class="form-control col-sm-7" name="email" placeholder="type email here" autofocus required style="font-size: 16px;" />
					    	</div>
						</div>						    

					  	<button type="submit" class="btn btn-primary btn-lg">Add More</button>
					</form>
	        	</div>
	        	<!-- end of login -->

	        	<hr />

	        	<div class="col-lg-12 col-md-8" id="contributor" style="margin-top: 40px">
	        		<header>
						<h1>Contributor</h1>

						<h3>Here is written who is entitled to get access to the admin page</h3>
						<h4>*note : make sure it have account on keepo</h4>
						<hr />
	        		</header>
					
					@if(Session::has('error'))
					    <div class="alert alert-danger"> 
					    	{!! Session::get('error') !!}
					    </div>
					@endif

					<form action="{{ route('accessUser') }}" method="POST">
						@if(!empty(@$user))
							@forelse($user as $item)
							  	<div class="form-row">
							    	<div class="form-group col-md-5">
							          		<input type="email" class="form-control col-sm-12" value="{{ $item }}" readonly="true" name="emailList[]" style="font-size: 16px;" />
							    	</div>
							    
							    	<div class="form-group col-md-2">
									  	<a href="{{ url('access/user/rm?key='.$item) }}" class="btn btn-danger"><i class="glyphicon glyphicon-remove" style="color: white;"></i></a>
							    	</div>
							  	</div>
							@empty
							@endforelse

						@else
							<h4><i>kosong</i></h4>
						@endif
							
						<div class="form-row">
					    	<div class="form-group col-md-9">
				          		<input type="email" class="form-control col-sm-7" name="email" placeholder="type email here" autofocus required style="font-size: 16px;" />
					    	</div>
						</div>						    

					  	<button type="submit" class="btn btn-primary btn-lg">Add More</button>
					</form>
	        	</div>
	        </div>
		</div>
	</div>
@endsection