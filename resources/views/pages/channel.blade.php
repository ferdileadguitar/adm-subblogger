@extends('layouts.app')

@push('css')
<link href="{{ asset('dist/css/channel.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('dist/js/channel.js') }}"></script>
@endpush

@section('content')
<!-- Tabs -->
<div class="tabs" ng-controller="tabs">
	<header class="tabs-header">
        <nav class="tabs-nav">
            <a ng-click="openTab($event, 'all');" class="box active">By Channel</a>
            <a ng-click="openTab($event, 'format');" class="box">By Format <span class="label label-danger"></span></a>
        </nav>
    </header>

    <div class="tabs-body">
        <div id="all" class="tab-component tab-open" ng-controller="allController">
            <tab></tab>
        </div>
        <div id="format" class="tab-component" ng-controller="formatController">
            <!-- Filters -->
            <!-- <tab></tab> -->
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
            </div>

            <!-- Table Flex -->
            <div class="tbls-houder" ng-hide="onLoad" ng-style="{'margin-top': '30px'}">
               <div class="tbls tbls-content">
                    <div class="tbls-label-count"><h4 ng-bind-html="countChannel"></h4></div>

                    <header class="tbls-header tbls-row flex-row">
                        <div class="tbls-col-1"></div>
                        <div class="tbls-col-6"><h6 class="text-uppercase">format</h6></div>
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
                        <div class="tbls-col-3 clickable" ng-click="onSort('avg-share')">
                            <h6 class="text-uppercase text-center full-width">Average Share</h6>  
                            <div class="sort-box" ng-class="{'on-select':sort.key == 'avg-share'}">
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
                        <div class="tbls-col-3 clickable" ng-click="onSort('avg-embed')">
                            <h6 class="text-uppercase text-center full-width">Average Embed</h6>  
                            <div class="sort-box" ng-class="{'on-select':sort.key == 'avg-embed'}">
                                <i class="icon-up-dir up-icon" ng-class="{'actived': !sort.reverse}"></i>
                                <i class="icon-down-dir down-icon" ng-class="{'actived': sort.reverse}"></i>
                            </div>
                        </div>
                    </header>

                    <div class="tbls-body tbls-stripped">
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

            <div class="loader" ng-hide="!onLoad">
	            <svg xmlns="http://www.w3.org/2000/svg" version="1.1"> <defs> <filter id="gooey"> <feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur"></feGaussianBlur> <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 18 -7" result="goo"></feColorMatrix> <feBlend in="SourceGraphic" in2="goo"></feBlend> </filter> </defs> </svg>
	            <div class="blob blob-0"></div> <div class="blob blob-1"></div> <div class="blob blob-2"></div> <div class="blob blob-3"></div> <div class="blob blob-4"></div> <div class="blob blob-5"></div>
	        </div>
        </div>
    </div>
</div>


<!-- Feeds -->
<script id="feedListTemplate" type="text/ng-template">
    <div class="box-list" ng-if="! _.isEmpty(data)" ng-repeat="channel in data track by $index">
        <div class="box with-border" ng-class="{'tbls-loading': channel.loading }">
            <div class="tbls-row">
                <div class="tbls-col-1"><h4 ng-bind="$index+1"></h4></div>
                <div class="tbls-col-6 text-left"><h4 class="text-left text-capitalize" ng-bind-html="channel.title"></h4></div>
                <div class="tbls-col-3">
                    <h4 ng-bind-html="channel.total_posts | number" ng-style="{'padding-bottom' : '15px'}"></h4>
                    <h6 class="text-uppercase">post <i class="icon down-dir"></i></h6>
                </div>
                <div class="tbls-col-3">
                	<h4 ng-bind-html="channel.total_views | number" ng-style="{'padding-bottom' : '15px'}"></h4>
                	<h6 class="text-uppercase">views</h6>
                </div>
                <div class="tbls-col-3">
                	<h4 ng-bind-html="channel.average_views | number" ng-style="{'padding-bottom' : '15px'}"></h4>
                	<h6 class="text-uppercase">average views</h6>
                </div>
                <div class="tbls-col-3">
                	<h4 ng-bind-html="channel.total_shares | number" ng-style="{'padding-bottom' : '15px'}"></h4>
                	<h6 class="text-uppercase">total shares</h6>
                </div>
                 <div class="tbls-col-3">
                	<h4 ng-bind-html="channel.average_shares | number" ng-style="{'padding-bottom' : '15px'}"></h4>
                	<h6 class="text-uppercase">average shares</h6>
                </div>
                <div class="tbls-col-3">
                	<h4 ng-bind-html="channel.total_embed | number" ng-style="{'padding-bottom' : '15px'}"></h4>
                	<h6 class="text-uppercase">total embed</h6>
                </div>
                <div class="tbls-col-3">
                	<h4 ng-bind-html="channel.average_embed | number" ng-style="{'padding-bottom' : '15px'}"></h4>
                	<h6 class="text-uppercase">average embed</h6>
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
            </div>

            <!-- Table Flex -->
            <div class="tbls-houder" ng-hide="onLoad" ng-style="{'margin-top' : '30px'}">
                <div class="tbls tbls-content">
                    <div class="tbls-label-count"><h4 ng-bind-html="countChannel"></h4></div>

                    <header class="tbls-header tbls-row flex-row">
                        <div class="tbls-col-1"></div>
                        <div class="tbls-col-6"><h6 class="text-uppercase">channel</h6></div>
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
                        <div class="tbls-col-3 clickable" ng-click="onSort('avg-share')">
                            <h6 class="text-uppercase text-center full-width">Average Share</h6>  
                            <div class="sort-box" ng-class="{'on-select':sort.key == 'avg-share'}">
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
                        <div class="tbls-col-3 clickable" ng-click="onSort('avg-embed')">
                            <h6 class="text-uppercase text-center full-width">Average Embed</h6>  
                            <div class="sort-box" ng-class="{'on-select':sort.key == 'avg-embed'}">
                                <i class="icon-up-dir up-icon" ng-class="{'actived': !sort.reverse}"></i>
                                <i class="icon-down-dir down-icon" ng-class="{'actived': sort.reverse}"></i>
                            </div>
                        </div>
                    </header>

                    <div class="tbls-body tbls-stripped">
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
