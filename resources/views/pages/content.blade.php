@extends('layouts.app')

@push('css')
<link href="{{ asset('dist/css/content.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('dist/js/content.js') }}"></script>
@endpush

@section('content')
<!-- Tabs -->
<div class="tabs" ng-controller="tabs" ng-init="init({moderationCount: {{ $moderationCount }}})">
    <header class="tabs-header">
        <nav class="tabs-nav">
            <a ng-click="openTab($event, 'all');" class="box active">All Contents</a>
            <a ng-click="openTab($event, 'moderation');" class="box">Need Moderation <span class="label label-danger" ng-bind="moderationCount"></span></a>
            <a ng-click="openTab($event, 'contributor');" class="box">Contributor Only</a>
        </nav>

        <aside class="adds">
            <div class="box drop" name="bulk-action" singleaction="bulkAction" ng-controller="dropdown">
                <div class="drop-component <% openList ? 'drop-open' : '' %>" ng-click="openList = (openList ? false : true)">
                    <span class="drop-component-text">Bulk Action</span>
                    <span class="glyphicon glyphicon-chevron-down"></span>
                </div>

                <ul class="box drop-list">
                    <li ng-click="select($event, 'approve');">Approve</li>
                    <li ng-click="select($event, 'moderate');">Moderate</li>
                    <li ng-click="select($event, 'reject');">Reject</li>
                    <li ng-click="select($event, 'sticky');">Sticky</li>
                    <li ng-click="select($event, 'premium');">Premium</li>
                    <li class="text-danger" ng-click="select($event, 'delete');">Delete</li>
                </ul>
            </div>
        </aside>
    </header>

    <div class="tabs-body">
        <div id="all" class="tab-component tab-open" ng-controller="allController">
            <tab></tab>
        </div>

        <div id="moderation" class="tab-component" ng-controller="moderationController">
            <tab></tab>
        </div>

        <div id="contributor" class="tab-component">
            <!-- Filters -->
            <div class="filters">
                <div class="filter-component">
                    <div class="box drop drop-large" name="filter-dateRange" ng-controller="dropdown">
                        <div class="drop-component <% openList ? 'drop-open' : '' %>" ng-click="openList = (openList ? false : true)">
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
                        <div class="drop-component <% openList ? 'drop-open' : '' %>" ng-click="openList = (openList ? false : true)">
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
                    <form action="javascript:;" class="box" ng-submit="submit($event)">
                        <input type="text" placeholder="Search Post">
                        <input type="submit" value="Search" class="btn btn-success">
                    </form>
                </div>
            </div>

            <!-- Table Flex -->
            <div class="tbls-houder">
               <div class="tbls tbls-content">
                    <header class="tbls-header tbls-row">
                        <div class="tbls-col-1"><input type="checkbox" ng-click="onCheckAll()"></div>
                        <div class="tbls-col-3">Author</div>
                        <div class="tbls-col-xl">Title</div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('channel')">Channel <span ng-show="sort.key == 'channel'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('format')">Format  <span ng-show="sort.key == 'format'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('view')">View  <span ng-show="sort.key == 'view'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('share')">Share  <span ng-show="sort.key == 'share'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('embed')">Embed  <span ng-show="sort.key == 'embed'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('created')">Created  <span ng-show="sort.key == 'created'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
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
    <div ng-if="! _.isEmpty(data)" ng-repeat="post in data">
        <div class="box" ng-class="{'bg-warning': (post.status == -2), 'bg-danger': (post.status == 0), 'bg-success': (post.status == 1), 'with-footer': (! post.status && post.reason), 'tbls-loading': post.loading }">
            <div class="tbls-row">
                <div class="tbls-col-1"><input type="checkbox" ng-click="onCheck(post)" ng-checked="post.checked"></div>
                <div class="tbls-col-3"><a href="<% post.user_url %>"><% post.user %></a></div>
                <div class="tbls-col-xl">
                    <div class="tbls-title-box">
                        <figure class="tbls-thumbnail" ng-if="post.image"><img ng-src="<% post.image %>"></figure>
                        <div class="tbls-title">
                            <div>
                                <article><% post.title %></article>
                                <footer>
                                    <a>Change Title</a>
                                    <a>View</a>
                                    <a>Edit Detail</a>
                                </footer>
                            </div>
                            <div class="tbls-tags">
                                <article ng-bind-html="convertTags(post.tags)"></article>
                                <footer>
                                    <a>Edit Tags</a>
                                </footer>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tbls-col-3">
                    <% post.channel %>
                    <div><a>Edit</a></div>
                </div>
                <div class="tbls-col-3"><% post.post_type %></div>
                <div class="tbls-col-3"><% post.views %></div>
                <div class="tbls-col-3"><% post.shares || 0 %></div>
                <div class="tbls-col-3"><% post.embeds || 0 %></div>
                <div class="tbls-col-3">
                    <p><% post.created %></p>
                    <div><a>Edit</a></div>
                </div>
                <div class="tbls-col-2 tbls-btn-group">
                    <div ng-hide="post.status == 2">
                        <div ng-if="post.status != 1"><a title="Approve" class="btn-round btn-ok" ng-click="setStatus(post, 1)"><span class="glyphicon glyphicon-ok"></span></a></div>
                        <div ng-if="post.status != -2"><a title="Moderate" class="btn-round btn-exclamation" ng-click="setStatus(post, -2)"><span></span></a></div>
                        <div ng-if="post.status != 0"><a title="Reject" class="btn-round btn-remove" ng-click="setStatus(post, 0)"><span class="glyphicon glyphicon-remove"></span></a></div>
                    </div>
                </div>
                <div class="tbls-col-3 tbls-btn-group">
                    <div><a class="btn" ng-class="{'btn-default': !post.is_sticky, 'btn-primary': post.is_sticky}" ng-click="setSticky(post)" ng-if="post.status != 2">Sticky</a></div>
                    <div><a class="btn" ng-class="{'btn-default': !post.is_premium, 'btn-success': post.is_premium}" ng-click="setPremium(post)" ng-if="post.status != 2">Premium</a></div>
                    <div><a class="btn btn-danger" ng-click="delete(post)">Delete</a></div>
                </div>
            </div>

            <footer class="tbls-footer" ng-show="!post.status && post.reason">
                <p>Rejected: <strong><% post.reason %></strong> <a>Change Reason</a></p>
            </footer>
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
                    <div class="box drop drop-large" name="filter-dateRange" ng-controller="dropdown">
                        <div class="drop-component <% openList ? 'drop-open' : '' %>" ng-click="openList = (openList ? false : true)">
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
                <div class="filter-component" ng-if="controller != 'moderated'">
                    <div class="box drop" name="filter-status" ng-controller="dropdown">
                        <div class="drop-component <% openList ? 'drop-open' : '' %>" ng-click="openList = (openList ? false : true)">
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
                    <form action="javascript:;" class="box" ng-submit="submit($event)">
                        <input type="text" ng-model="searchInput" placeholder="Search Post">
                        <input type="submit" value="Search" class="btn btn-success">
                    </form>
                </div>
            </div>

            <!-- Table Flex -->
            <div class="tbls-houder" ng-hide="onLoad">
                <div class="tbls tbls-content">
                    <header class="tbls-header tbls-row">
                        <div class="tbls-col-1"><input type="checkbox" ng-click="onCheckAll()"></div>
                        <div class="tbls-col-3">Author</div>
                        <div class="tbls-col-xl">Title</div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('channel')">Channel <span ng-show="sort.key == 'channel'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('format')">Format  <span ng-show="sort.key == 'format'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('view')">View  <span ng-show="sort.key == 'view'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('share')">Share  <span ng-show="sort.key == 'share'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('embed')">Embed  <span ng-show="sort.key == 'embed'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('created')">Created  <span ng-show="sort.key == 'created'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
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
                <a ng-if="!singleButton" ng-class="{'remove': (type == 'delete'), 'ok': (type != 'delete')}" ng-click="okCallback ? callback(okCallback) : close()"><% okText || 'Yes' %></a>
                <a ng-click="closeCallback ? callback(closeCallback) : close()"><% cancelText || 'No' %></a>
            </footer>

            <a class="mdl-close" ng-click="close()">&times;</a>
        </div>
    </div>
</script>
@endsection
