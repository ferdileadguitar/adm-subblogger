@extends('layouts.app')

@push('css')
<link href="{{ asset('dist/css/authors.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('dist/js/authors.js') }}"></script>
@endpush

@section('content')
<!-- Tabs -->
<div class="tabs" ng-controller="tabs">
    <div class="tabs-body">
        <div id="all" class="tab-component tab-open" ng-controller="allController">
            <tab></tab>
        </div>
        <div id="contributor" class="tab-component">
            <!-- Filters -->
            <!-- <tab></tab> -->
            <div class="filters">
                <div class="filter-component">
                    <div class="box drop drop-large" name="filter-dateRange" ng-controller="dropdown">
                        <div class="drop-component <@ openList ? 'drop-open' : '' @>" ng-click="openList = (openList ? false : true)">
                            <span class="drop-component-text">All Time</span>
                            <span class="glyphicon glyphicon-chevron-down"></span>
                        </div>
                        <ul class="box drop-list">
                            <li ng-click="select($event, 'today');">Today</li>
                            <li ng-click="select($event, 'yesterday');">Yesterday</li>
                            <li ng-click="select($event, 'last-7-days');">Last 7 days</li>
                            <li ng-click="select($event, 'last-30-days');">Last 30 days</li>
                            <li ng-click="select($event, 'last-90-days');">Last 90 days</li>
                            <li ng-click="select($event, 'this-month');">This Month</li>
                            <li ng-click="select($event, 'this-year');">This Year</li>
                            <li ng-click="select($event, 'all-time');">All Time</li>
                            <li class="drop-list-addon">
                                <div class="date-range-component">
                                    <input type="date" ng-model="startDate">
                                    <span>-</span>
                                    <input type="date" ng-model="endDate">
                                </div>
                                <a class="btn btn-primary" ng-click="selectDate($event)">Select</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="filter-component">
                    <div class="box drop" name="filter-status" ng-controller="dropdown">
                        <div class="drop-component <@ openList ? 'drop-open' : '' @>" ng-click="openList = (openList ? false : true)">
                            <span class="drop-component-text">All Status</span>
                            <span class="glyphicon glyphicon-chevron-down"></span>
                        </div>
                        <ul class="box drop-list">
                            <li ng-click="select($event, 'all-status');">All Status</li>
                            <li ng-click="select($event, 'private');">Private</li>
                            <li ng-click="select($event, 'public');">Public</li>
                            <li ng-click="select($event, 'approved');">Approved</li>
                            <li ng-click="select($event, 'moderated');">Moderated</li>
                            <li ng-click="select($event, 'rejected');">Rejected</li>
                        </ul>
                    </div>
                </div>

                <div class="filter-component filter-full search" ng-controller="search">
                    <form action="javascript:;" class="box box-xs" ng-submit="submit($event)">
                        <input type="text" ng-model="searchInput" placeholder="Search Author">
                        <input type="submit" value="Search" class="btn btn-success">
                    </form>
                </div>
            </div>

            <!-- Table Flex -->
            <div class="tbls-houder" ng-style="{'margin-top' : '30px'}">
               <div class="tbls tbls-content">
                    <div class="tbls-label-count"><h4 ng-bind-html="countAuthor"></h4></div>

                    <header class="tbls-header tbls-row">
                        <div class="tbls-col-1"><input type="checkbox" ng-click="onCheckAll()"></div>
                        <!-- <div class="tbls-col-3">Author</div> -->
                        <!-- <div class="tbls-col-xl">Title</div> -->
                        <div class="tbls-col-3 clickable" ng-click="onSort('post')">Post <span ng-show="sort.key == 'post'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('view')">Total View  <span ng-show="sort.key == 'view'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('view')">Average View  <span ng-show="sort.key == 'view'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('share')">Total Share  <span ng-show="sort.key == 'share'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('embed')">Total Embed  <span ng-show="sort.key == 'embed'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('email')">Email  <span ng-show="sort.key == 'created'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-2"></div>
                        <div class="tbls-col-3"></div>
                    </header>

                    <div class="tbls-body">
                        <feeds></feeds>  
                    </div>
                </div>

                <footer class="tbls-pagination" ng-show="pageCount > 1">
                    <div class="tbls-pagi">
                        <span>Page</span> 
                        <select ng-model="pageCurrent" ng-options="num for num in _.range(1, pageCount+1)" ng-change="changePage()"></select>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</div>


<!-- Feeds -->
<script id="feedListTemplate" type="text/ng-template">
    <div ng-if="! _.isEmpty(data)" ng-repeat="author in data track by $index">
        <div class="box with-border" ng-class="{'tbls-loading': author.loading }">
            <div class="tbls-row">
                <div class="tbls-col-1"><h4 ng-bind="$index+1"></h4></div>
                <div class="tbls-col-6 text-left"><a href="javascript:0;" class="full-width"><h4 class="text-left"><@ author.username @></h4></a></div>
                <div class="tbls-col-3">
                    <h4 ng-bind-html="author.total_posts | number"></h4>
                    <h6 class="text-uppercase">post</h6>
                </div>
                <div class="tbls-col-3">
                	<h4 ng-bind-html="author.total_views | number"></h4>
                	<h6 class="text-uppercase">views</h6>
                </div>
                <div class="tbls-col-3">
                	<h4 ng-bind-html="author.average_views | number"></h4>
                	<h6 class="text-uppercase">average views</h6>
                </div>
                <div class="tbls-col-3">
                	<h4 ng-bind-html="author.total_shares | number"></h4>
                	<h6 class="text-uppercase">total shares</h6>
                </div>
                <div class="tbls-col-3">
                	<h4 ng-bind-html="author.total_embed | number"></h4>
                	<h6 class="text-uppercase">total embed</h6>
                </div>
                <div class="tbls-col-8">
	                <h4 class="text-left" ng-bind-html="(author.email).length ? author.email : 'email not set'"></h4>
                </div>
                <div class="tbls-col-3 tbls-btn-group">
                    <div class="full-width"><a class="btn btn-border-danger text-uppercase" ng-click="setPassword(author, $index)">Reset Pas</a></div>
                    <div class="full-width"><a class="btn btn-border-danger text-uppercase" ng-click="delete(author)">Delete</a></div>
                </div>
            </div>
        </div>
    </div>
</script>

<!-- Tab -->
<script id="tabTemplate" type="text/ng-template">
    <div>
        <div>
            <!-- Filters -->
            <div class="filters">
                <div class="filter-component">
                    <div class="box drop drop-small" name="filter-dateRange" ng-controller="dropdown">
                        <div class="drop-component <@ openList ? 'drop-open' : '' @>" ng-click="openList = (openList ? false : true)">
                            <span class="drop-component-text">All Time</span>
                            <span class="glyphicon glyphicon-chevron-down"></span>
                        </div>
                        <ul class="box drop-list">
                            <li ng-click="select($event, 'today');">Today</li>
                            <li ng-click="select($event, 'yesterday');">Yesterday</li>
                            <li ng-click="select($event, 'last-7-days');">Last 7 days</li>
                            <li ng-click="select($event, 'last-30-days');">Last 30 days</li>
                            <li ng-click="select($event, 'last-90-days');">Last 90 days</li>
                            <li ng-click="select($event, 'this-month');">This Month</li>
                            <li ng-click="select($event, 'this-year');">This Year</li>
                            <li ng-click="select($event, 'all-time');">All Time</li>
                            <li class="drop-list-addon">
                                <div class="date-range-component">
                                    <input type="date" ng-model="startDate">
                                    <span>-</span>
                                    <input type="date" ng-model="endDate">
                                </div>
                                <a class="btn btn-primary" ng-click="selectDate($event)">Select</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="filter-component filter-full search" ng-controller="search">
                    <form action="javascript:;" class="box box-xs" ng-submit="submit($event)">
                        <input type="text" ng-model="searchInput" placeholder="Search Author">
                        <input type="submit" value="Search" class="btn btn-success">
                    </form>
                </div>

                <aside class="adds">
		            <div class="box drop" name="bulk-action" singleaction="bulkAction" ng-controller="dropdown">
		                <div class="drop-component <@ openList ? 'drop-open' : '' @>" ng-click="openList = (openList ? false : true)">
		                    <span class="drop-component-text">Bulk Action</span>
		                    <span class="glyphicon glyphicon-chevron-down"></span>
		                </div>

		                <ul class="box drop-list">
		                    <li ng-click="select($event, 'set-password');">Set Password</li>
		                    <li ng-click="select($event, 'delete');">Delete</li>
		                </ul>
		            </div>
		        </aside>
            </div>

            <!-- Table Flex -->
            <div class="tbls-houder" ng-hide="onLoad" ng-style="{'margin-top' : '30px'}">
                <div class="tbls tbls-content">
                    <div class="tbls-label-count"><h4 ng-bind-html="countAuthor"></h4></div>

                    <header class="tbls-header tbls-row flex-row">
                        <div class="tbls-col-1"></div>
                        <div class="tbls-col-6"><h6 class="text-uppercase">Author</h6></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('post')">
                            <h6 class="text-uppercase text-center full-width">Post</h6> 
                            <div class="sort-box" ng-class="{'on-select':sort.key == 'post'}">
                                <i class="icon-up-dir up-icon" ng-class="{'actived': !sort.reverse}"></i>
                                <i class="icon-down-dir down-icon" ng-class="{'actived': sort.reverse}"></i>
                            </div>
                        </div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('view')">
                            <h6 class="text-uppercase text-center full-width">Total View</h6>  
                            <div class="sort-box" ng-class="{'on-select':sort.key == 'view'}">
                                <i class="icon-up-dir up-icon" ng-class="{'actived': !sort.reverse}"></i>
                                <i class="icon-down-dir down-icon" ng-class="{'actived': sort.reverse}"></i>
                            </div>
                        </div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('avg-view')">
                            <h6 class="text-uppercase text-center full-width">Average View</h6>  
                            <div class="sort-box" ng-class="{'on-select':sort.key == 'avg-view'}">
                                <i class="icon-up-dir up-icon" ng-class="{'actived': !sort.reverse}"></i>
                                <i class="icon-down-dir down-icon" ng-class="{'actived': sort.reverse}"></i>
                            </div>
                        </div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('share')">
                            <h6 class="text-uppercase text-center full-width">Total Share</h6>  
                            <div class="sort-box" ng-class="{'on-select':sort.key == 'share'}">
                                <i class="icon-up-dir up-icon" ng-class="{'actived': !sort.reverse}"></i>
                                <i class="icon-down-dir down-icon" ng-class="{'actived': sort.reverse}"></i>
                            </div>
                        </div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('embed')">
                            <h6 class="text-uppercase text-center full-width">Total Embed</h6>  
                            <div class="sort-box" ng-class="{'on-select':sort.key == 'embed'}">
                                <i class="icon-up-dir up-icon" ng-class="{'actived': !sort.reverse}"></i>
                                <i class="icon-down-dir down-icon" ng-class="{'actived': sort.reverse}"></i>
                            </div>
                        </div>
                        <div class="tbls-col-8 clickable" ng-click="onSort()" ng-style="{'justify-content':'center'}">
                            <h6 class="text-uppercase">Email</h6>  
                        </div>
                        <div class="tbls-col-3"></div>
                    </header>

                    <div class="tbls-body">
                        <feeds></feeds> 
                    </div>
                </div>

                <footer class="tbls-pagination" ng-show="pageCount > 1">
                    <div class="tbls-pagi">
                        <span>Page</span> 
                        <select ng-model="pageCurrent" ng-options="num for num in _.range(1, pageCount+1)" ng-change="changePage()"></select>
                    </div>
                </footer>
            </div>
        </div>

        <div class="loader" ng-hide="!onLoad">
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1"> <defs> <filter id="gooey"> <feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur"></feGaussianBlur> <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 18 -7" result="goo"></feColorMatrix> <feBlend in="SourceGraphic" in2="goo"></feBlend> </filter> </defs> </svg>
            <div class="blob blob-0"></div> <div class="blob blob-1"></div> <div class="blob blob-2"></div> <div class="blob blob-3"></div> <div class="blob blob-4"></div> <div class="blob blob-5"></div>
        </div>
    </div>
</script>

<!-- Modal -->
<script id="modalTemplate" type="text/ng-template">
    <div class="mdl mdl-popup mdl-open">
        <div class="mdl-component">
            <article class="mdl-body">
                <p ng-if="type == 'text' || type == 'delete'" ng-bind-html="text"></p>
            </article>
            <footer class="mdl-footer">
                <a ng-if="!singleButton" ng-class="{'remove': (type == 'delete'), 'ok': (type != 'delete')}" ng-click="okCallback ? callback(okCallback) : close()"><@ okText || 'Yes' @></a>
                <a ng-click="closeCallback ? callback(closeCallback) : close()"><@ cancelText || 'No' @></a>
            </footer>

            <a class="mdl-close" ng-click="close()">&times;</a>
        </div>
    </div>
</script>
@endsection
