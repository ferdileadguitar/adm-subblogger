@extends('layouts.app')

@push('css')
<link href="{{ asset('dist/css/content.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('dist/js/content-user.js') }}"></script>
@endpush

@section('content')
<!-- Tabs -->
<div class="tabs" ng-controller="tabs">
    <header class="tabs-header">
        <nav class="tabs-nav">
            <a ng-click="openTab($event, 'all');" class="box active">All Contents</a>
            <a ng-click="openTab($event, 'moderation');" class="box">Need Moderation <span class="label label-danger" ng-bind="moderatedTop"></span></a>
        </nav>

        <aside class="adds">
            <div class="box drop" name="bulk-action" singleaction="bulkAction" ng-controller="dropdown">
                <div class="drop-component <@ openList ? 'drop-open' : '' @>" ng-click="openList = (openList ? false : true)">
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
                    <form action="javascript:;" class="box" ng-submit="submit($event)">
                        <input type="text" ng-model="searchInput" placeholder="Search Post">
                        <input type="submit" value="Search" class="btn btn-success">
                    </form>
                </div>
            </div>

            <!-- Table Flex -->
            <div class="tbls-houder" ng-style="{'margin-top': '30px'}">
               <div class="tbls tbls-content">
                    <div class="tbls-label-count"><h4 ng-bind-html="countPost"></h4></div>

                    <header class="tbls-header tbls-row">
                        <div class="tbls-col-1"><input type="checkbox" ng-click="onCheckAll()"></div>
                        <div class="tbls-col-3"><h6 class="text-uppercase">Author</h6></div>
                        <div class="tbls-col-xl"><h6 class="text-uppercase">Title</h6></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('channel')"><h6 class="text-uppercase full-width">Channel<h6 class="text-uppercase full-width"> <span ng-show="sort.key == 'channel'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('format')"><h6 class="text-uppercase full-width">Format</h6>  <span ng-show="sort.key == 'format'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('view')"><h6 class="text-uppercase full-width">View</h6>  <span ng-show="sort.key == 'view'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('share')"><h6 class="text-uppercase full-width">Share</h6>  <span ng-show="sort.key == 'share'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('embed')"><h6 class="text-uppercase full-width">Embed</h6>  <span ng-show="sort.key == 'embed'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('created')"><h6 class="text-uppercase full-width">Created</h6>  <span ng-show="sort.key == 'created'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
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
    <div ng-if="! _.isEmpty(data)" ng-repeat="post in data track by $index">
        <div class="box" ng-class="{'bg-warning': (post.status == -2), 'bg-danger': (post.status == 0), 'bg-success': (post.status == 1), 'with-footer': (! post.status && post.reason), 'tbls-loading': post.loading }">
            <div class="tbls-row">
                <div class="tbls-col-1"><input type="checkbox" ng-click="onCheck(post)" ng-checked="post.checked"></div>
                <!-- <div class="tbls-col-3"><a ng-href="<@ filters.status | usersParseLinks:(post.user.slug) @>"><@ post.user.display_name @></a></div> -->
                <div class="tbls-col-3"><a ng-href="<@ post.user.url @>"><@ post.user.display_name @></a></div>
                <div class="tbls-col-xl">
                    <div class="tbls-title-box">
                        <figure class="tbls-thumbnail" ng-click="changeCover(post, 'image', $index)" ng-class="{ 'can-change' : showFeature.imgCover(post) }" ng-attr-title="<@ showFeature.title(post) @>"><img ng-src="<@ (post.image.url) ? post.image.url : '{{ url('img/tile-no-image.jpg') }}' @>"></figure>
                        <div class="tbls-title">
                            <div>
                                <article ng-bind-html="post.title"></article>
                                <footer>
                                    <a ng-click="setEditor(post, 'title', $index)">Change Title</a>
                                    <a ng-click="parseFeedsLink(post)" ng-if="showFeature.viewLink(post)">View</a>
                                    <a ng-click="setEditor(post, post.post_type, $index)" ng-if="showFeature.editorLink(post)">Edit Detail</a>
                                </footer>
                            </div>
                            <div class="tbls-tags">
                                <article ng-bind-html="convertTags(post.tags)"></article>
                                <footer>
                                    <a ng-click="setEditor(post, 'tags', $index)">Edit Tags</a>
                                </footer>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tbls-col-3">
                    <@ post.channel.name @>
                    <div><a ng-click="setEditor(post, 'channel', $index)" ng-if="showFeature.editorChannel(post)">Edit</a></div>
                </div>
                <div class="tbls-col-3"><@ post.post_type @></div>
                <div class="tbls-col-3"><@ (post.views | number) @></div>
                <div class="tbls-col-3"><@ post.shares || 0 @></div>
                <div class="tbls-col-3"><@ post.embeds || 0 @></div>
                <div class="tbls-col-3">
                    <p><@ post.created @></p>
                    <div><a ng-click="setEditor(post, 'created', $index)">Edit</a></div>
                </div>
                <div class="tbls-col-2 tbls-btn-group">
                    <div ng-hide="post.status == 2">
                        <div ng-if="post.status != 1"><a title="Approve" class="btn-round btn-ok" ng-click="setStatus(post, 1)"><span class="glyphicon glyphicon-ok"></span></a></div>
                        <div ng-if="post.status != -2"><a title="Moderate" class="btn-round btn-exclamation" ng-click="setStatus(post, -2)"><span></span></a></div>
                        <div ng-if="post.status != 0"><a title="Reject" class="btn-round btn-remove" ng-click="setStatus(post, 0)"><span class="glyphicon glyphicon-remove"></span></a></div>
                        <div ng-if="!post.is_up_contents"><a title="Up Content" class="btn-round btn-up-content" ng-click="setEditor(post, 'up-content', $index)"><span class="glyphicon glyphicon-chevron-up"></span></a></div>
                    </div>
                </div>
                <div class="tbls-col-3 tbls-btn-group">
                    <div ng-if="showFeature.editorPost(post)"><a class="btn" ng-class="{'btn-default': !post.is_sticky, 'btn-primary': post.is_sticky}" ng-click="setSticky(post)" ng-if="post.status != 2">Sticky</a></div>
                    <div ng-if="showFeature.editorPost(post)"><a class="btn" ng-class="{'btn-default': !post.is_premium, 'btn-success': post.is_premium}" ng-click="setPremium(post)" ng-if="post.status != 2">Premium</a></div>
                    <div><a class="btn btn-danger" ng-click="delete(post)">Delete</a></div>
                </div>
            </div>

            <footer class="tbls-footer" ng-show="!post.status">
                 <p>Rejected by <span ng-bind="posts.rejected.user ? posts.rejected.user : 'anonymous'" ng-style="{'text-transform' : 'capitalize'}"></span> : <strong ng-bind="posts.rejected.msg ? posts.rejected.msg : 'No Reason'"></strong> <a>Change Reason</a></p>
            </footer>

            <footer class="tbls-footer" ng-show="!post.status">
                <p>Rejected by <span ng-bind="posts.rejected.user ? posts.rejected.user : 'Anonymous'"></span> : <strong ng-bind="posts.rejected.msg ? posts.rejected.msg : 'No Reason'"></strong> <a>Change Reason</a></p>
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
                <div class="filter-component" ng-if=" _.contains(['all', 'contributor'], controller)">
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
                    <form action="javascript:;" class="box" ng-submit="submit($event)">
                        <input type="text" ng-model="searchInput" placeholder="Search Post">
                        <input type="submit" value="Search" class="btn btn-success">
                    </form>
                </div>
            </div>

            <!-- Table Flex -->
            <div class="tbls-houder" ng-hide="onLoad" ng-style="{'margin-top' : '30px'}">
                <div class="tbls tbls-content">
                    <div class="tbls-label-count"><h4 ng-bind-html="countPost"></h4></div>

                    <header class="tbls-header tbls-row">
                        <div class="tbls-col-1"><input type="checkbox" ng-click="onCheckAll()"></div>
                        <div class="tbls-col-3"><h6 class="text-uppercase full-width">Author</h6></div>
                        <div class="tbls-col-xl"><h6 class="text-uppercase full-width">Title<h6 class="text-uppercase full-width"></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('channel')"><h6 class="text-uppercase full-width">Channel</h6> <span ng-show="sort.key == 'channel'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('format')"><h6 class="text-uppercase full-width">Format</h6> <span ng-show="sort.key == 'format'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('view')"><h6 class="text-uppercase full-width">View</h6> <span ng-show="sort.key == 'view'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('share')"><h6 class="text-uppercase full-width">Share</h6> <span ng-show="sort.key == 'share'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('embed')"><h6 class="text-uppercase full-width">Embed</h6> <span ng-show="sort.key == 'embed'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
                        <div class="tbls-col-3 clickable" ng-click="onSort('created')"><h6 class="text-uppercase full-width">Created</h6> <span ng-show="sort.key == 'created'" ng-class="{'glyphicon glyphicon-chevron-up':!sort.reverse,'glyphicon glyphicon-chevron-down':sort.reverse}"></span></div>
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
                <a ng-if="!singleButton" ng-class="{'remove': (type == 'delete'), 'ok': (type != 'delete')}" ng-click="okCallback ? callback(okCallback) : close()"><@ okText || 'Yes' @></a>
                <a ng-click="closeCallback ? callback(closeCallback) : close()"><@ cancelText || 'No' @></a>
            </footer>

            <a class="mdl-close" ng-click="close()">&times;</a>
        </div>
    </div>
</script>

<!-- Additional variable -->
<script type="text/javascript">window.user = {id: '{{ $user_id }}', name: '{{ $username }}'};</script>
@endsection
