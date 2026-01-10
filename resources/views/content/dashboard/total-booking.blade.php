@extends('layouts/contentNavbarLayout')

@section('title', 'Total booking')

@section('content')
<div class="content-wrapper">

                <!-- Content -->
                                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="row g-6">
  <!-- Card Border Shadow -->
  <div class="col-lg-3 col-sm-6">
    <div class="card card-border-shadow-primary h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-primary"><i class="icon-base bx bxs-truck icon-lg"></i></span>
          </div>
          <h4 class="mb-0">42</h4>
        </div>
        <p class="mb-2">On route vehicles</p>
        <p class="mb-0">
          <span class="text-heading fw-medium me-2">+18.2%</span>
          <span class="text-body-secondary">than last week</span>
        </p>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-sm-6">
    <div class="card card-border-shadow-warning h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-warning"><i class="icon-base bx bx-error icon-lg"></i></span>
          </div>
          <h4 class="mb-0">8</h4>
        </div>
        <p class="mb-2">Vehicles with errors</p>
        <p class="mb-0">
          <span class="text-heading fw-medium me-2">-8.7%</span>
          <span class="text-body-secondary">than last week</span>
        </p>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-sm-6">
    <div class="card card-border-shadow-danger h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-danger"><i class="icon-base bx bx-git-repo-forked icon-lg"></i></span>
          </div>
          <h4 class="mb-0">27</h4>
        </div>
        <p class="mb-2">Deviated from route</p>
        <p class="mb-0">
          <span class="text-heading fw-medium me-2">+4.3%</span>
          <span class="text-body-secondary">than last week</span>
        </p>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-sm-6">
    <div class="card card-border-shadow-info h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-info"><i class="icon-base bx bx-time-five icon-lg"></i></span>
          </div>
          <h4 class="mb-0">13</h4>
        </div>
        <p class="mb-2">Late vehicles</p>
        <p class="mb-0">
          <span class="text-heading fw-medium me-2">-2.5%</span>
          <span class="text-body-secondary">than last week</span>
        </p>
      </div>
    </div>
  </div>
  <!--/ Card Border Shadow -->
  <!-- Vehicles overview -->
  <div class="col-xxl-6">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between gap-2">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Vehicles Overview</h5>
        </div>
        <div class="dropdown">
          <button class="btn p-0" type="button" id="vehiclesOverview" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-base bx bx-dots-vertical-rounded icon-lg text-body-secondary"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="vehiclesOverview">
            <a class="dropdown-item" href="javascript:void(0);">Select All</a>
            <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
            <a class="dropdown-item" href="javascript:void(0);">Share</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="d-none d-lg-flex vehicles-progress-labels mb-6">
          <div class="vehicles-progress-label on-the-way-text" style="width: 39.7%;">On the way</div>
          <div class="vehicles-progress-label unloading-text" style="width: 28.3%;">Unloading</div>
          <div class="vehicles-progress-label loading-text" style="width: 17.4%;">Loading</div>
          <div class="vehicles-progress-label waiting-text text-nowrap" style="width: 14.6%;">Waiting</div>
        </div>
        <div class="vehicles-overview-progress progress rounded-3 mb-6 bg-transparent overflow-hidden" style="height: 46px;">
          <div class="progress-bar fw-medium text-start shadow-none bg-lighter text-heading px-4 rounded-0" role="progressbar" style="width: 39.7%" aria-valuenow="39.7" aria-valuemin="0" aria-valuemax="100">39.7%
          </div>
          <div class="progress-bar fw-medium text-start shadow-none bg-primary px-4" role="progressbar" style="width: 28.3%" aria-valuenow="28.3" aria-valuemin="0" aria-valuemax="100">28.3%</div>
          <div class="progress-bar fw-medium text-start shadow-none text-bg-info px-2 px-sm-4" role="progressbar" style="width: 17.4%" aria-valuenow="17.4" aria-valuemin="0" aria-valuemax="100">17.4%</div>
          <div class="progress-bar fw-medium text-start shadow-none snackbar text-paper px-1 px-sm-3 rounded-0 px-lg-4" role="progressbar" style="width: 14.6%" aria-valuenow="14.6" aria-valuemin="0" aria-valuemax="100">14.6%
          </div>
        </div>
        <div class="table-responsive">
          <table class="table card-table table-border-top-0">
            <tbody class="table-border-bottom-0">
              <tr>
                <td class="w-50 ps-0">
                  <div class="d-flex justify-content-start align-items-center">
                    <div class="me-2">
                      <i class="icon-base bx bx-car icon-lg text-heading"></i>
                    </div>
                    <h6 class="mb-0 fw-normal">On the way</h6>
                  </div>
                </td>
                <td class="text-end pe-0 text-nowrap">
                  <h6 class="mb-0">2hr 10min</h6>
                </td>
                <td class="text-end pe-0">
                  <span>39.7%</span>
                </td>
              </tr>
              <tr>
                <td class="w-50 ps-0">
                  <div class="d-flex justify-content-start align-items-center">
                    <div class="me-2">
                      <i class="icon-base bx bx-down-arrow-circle icon-lg text-heading"></i>
                    </div>
                    <h6 class="mb-0 fw-normal">Unloading</h6>
                  </div>
                </td>
                <td class="text-end pe-0 text-nowrap">
                  <h6 class="mb-0">3hr 15min</h6>
                </td>
                <td class="text-end pe-0">
                  <span>28.3%</span>
                </td>
              </tr>
              <tr>
                <td class="w-50 ps-0">
                  <div class="d-flex justify-content-start align-items-center">
                    <div class="me-2">
                      <i class="icon-base bx bx-up-arrow-circle icon-lg text-heading"></i>
                    </div>
                    <h6 class="mb-0 fw-normal">Loading</h6>
                  </div>
                </td>
                <td class="text-end pe-0 text-nowrap">
                  <h6 class="mb-0">1hr 24min</h6>
                </td>
                <td class="text-end pe-0">
                  <span>17.4%</span>
                </td>
              </tr>
              <tr>
                <td class="w-50 ps-0">
                  <div class="d-flex justify-content-start align-items-center">
                    <div class="me-2">
                      <i class="icon-base bx bx-time-five icon-lg text-heading"></i>
                    </div>
                    <h6 class="mb-0 fw-normal">Waiting</h6>
                  </div>
                </td>
                <td class="text-end pe-0 text-nowrap">
                  <h6 class="mb-0">5hr 19min</h6>
                </td>
                <td class="text-end pe-0">
                  <span>14.6%</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!--/ Vehicles overview -->
  <!-- Shipment statistics-->
  <div class="col-xxl-6 col-lg-7">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="mb-1">Shipment statistics</h5>
          <p class="card-subtitle">Total number of deliveries 23.8k</p>
        </div>
        <div class="btn-group">
          <button type="button" class="btn btn-label-primary">January</button>
          <button type="button" class="btn btn-label-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="visually-hidden">Toggle Dropdown</span>
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="javascript:void(0);">January</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">February</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">March</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">April</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">May</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">June</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">July</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">August</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">September</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">October</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">November</a></li>
            <li><a class="dropdown-item" href="javascript:void(0);">December</a></li>
          </ul>
        </div>
      </div>
      <div class="card-body">
        <div id="shipmentStatisticsChart" style="min-height: 320px;"><div id="apexchartswiu84sywg" class="apexcharts-canvas apexchartswiu84sywg apexcharts-theme-" style="width: 544px; height: 320px;"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" class="apexcharts-svg" xmlns:data="ApexChartsNS" transform="translate(0, 0)" width="544" height="320"><foreignObject x="0" y="0" width="544" height="320"><div class="apexcharts-legend apexcharts-align-center apx-legend-position-bottom" xmlns="http://www.w3.org/1999/xhtml" style="height: 40px; right: 0px; position: absolute; left: 0px; top: 280px;"><div class="apexcharts-legend-series" rel="1" seriesname="Shipment" data:collapsed="false" style="margin: 0px 10px;"><span class="apexcharts-legend-marker" rel="1" data:collapsed="false" style="height: 8px; width: 8px; left: -3px; top: 0px;"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="100%" height="100%"><path d="M 0, 0 
           m -4, 0 
           a 4,4 0 1,0 8,0 
           a 4,4 0 1,0 -8,0" fill="var(--bs-warning)" fill-opacity="1" stroke="var(--bs-primary)" stroke-opacity="0.9" stroke-linecap="round" stroke-width="0" stroke-dasharray="0" cx="0" cy="0" shape="circle" class="apexcharts-legend-marker apexcharts-marker apexcharts-marker-circle" style="transform: translate(50%, 50%);"></path></svg></span><span class="apexcharts-legend-text" rel="1" i="0" data:default-text="Shipment" data:collapsed="false" style="color: var(--bs-heading-color); font-size: 15px; font-weight: 400; font-family: var(--bs-font-family-base);">Shipment</span></div><div class="apexcharts-legend-series" rel="2" seriesname="Delivery" data:collapsed="false" style="margin: 0px 10px;"><span class="apexcharts-legend-marker" rel="2" data:collapsed="false" style="height: 8px; width: 8px; left: -3px; top: 0px;"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="100%" height="100%"><path d="M 0, 0 
           m -4, 0 
           a 4,4 0 1,0 8,0 
           a 4,4 0 1,0 -8,0" fill="var(--bs-primary)" fill-opacity="1" stroke="var(--bs-primary)" stroke-opacity="0.9" stroke-linecap="round" stroke-width="0" stroke-dasharray="0" cx="0" cy="0" shape="circle" class="apexcharts-legend-marker apexcharts-marker apexcharts-marker-circle" style="transform: translate(50%, 50%);"></path></svg></span><span class="apexcharts-legend-text" rel="2" i="1" data:default-text="Delivery" data:collapsed="false" style="color: var(--bs-heading-color); font-size: 15px; font-weight: 400; font-family: var(--bs-font-family-base);">Delivery</span></div></div><style type="text/css">
      .apexcharts-flip-y {
        transform: scaleY(-1) translateY(-100%);
        transform-origin: top;
        transform-box: fill-box;
      }
      .apexcharts-flip-x {
        transform: scaleX(-1);
        transform-origin: center;
        transform-box: fill-box;
      }
      .apexcharts-legend {
        display: flex;
        overflow: auto;
        padding: 0 10px;
      }
      .apexcharts-legend.apexcharts-legend-group-horizontal {
        flex-direction: column;
      }
      .apexcharts-legend-group {
        display: flex;
      }
      .apexcharts-legend-group-vertical {
        flex-direction: column-reverse;
      }
      .apexcharts-legend.apx-legend-position-bottom, .apexcharts-legend.apx-legend-position-top {
        flex-wrap: wrap
      }
      .apexcharts-legend.apx-legend-position-right, .apexcharts-legend.apx-legend-position-left {
        flex-direction: column;
        bottom: 0;
      }
      .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-left, .apexcharts-legend.apx-legend-position-top.apexcharts-align-left, .apexcharts-legend.apx-legend-position-right, .apexcharts-legend.apx-legend-position-left {
        justify-content: flex-start;
        align-items: flex-start;
      }
      .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-center, .apexcharts-legend.apx-legend-position-top.apexcharts-align-center {
        justify-content: center;
        align-items: center;
      }
      .apexcharts-legend.apx-legend-position-bottom.apexcharts-align-right, .apexcharts-legend.apx-legend-position-top.apexcharts-align-right {
        justify-content: flex-end;
        align-items: flex-end;
      }
      .apexcharts-legend-series {
        cursor: pointer;
        line-height: normal;
        display: flex;
        align-items: center;
      }
      .apexcharts-legend-text {
        position: relative;
        font-size: 14px;
      }
      .apexcharts-legend-text *, .apexcharts-legend-marker * {
        pointer-events: none;
      }
      .apexcharts-legend-marker {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        margin-right: 1px;
      }

      .apexcharts-legend-series.apexcharts-no-click {
        cursor: auto;
      }
      .apexcharts-legend .apexcharts-hidden-zero-series, .apexcharts-legend .apexcharts-hidden-null-series {
        display: none !important;
      }
      .apexcharts-inactive-legend {
        opacity: 0.45;
      }

    </style></foreignObject><rect width="0" height="0" x="0" y="0" rx="0" ry="0" opacity="1" stroke-width="0" stroke="none" stroke-dasharray="0" fill="#fefefe"></rect><g class="apexcharts-datalabels-group" transform="translate(0, 0) scale(1)"></g><g class="apexcharts-datalabels-group" transform="translate(0, 0) scale(1)"></g><g class="apexcharts-yaxis" rel="0" transform="translate(34.44218826293945, 0)"><g class="apexcharts-yaxis-texts-g"><text x="20" y="34.333333333333336" text-anchor="end" dominant-baseline="auto" font-size="13px" font-family="var(--bs-font-family-base)" font-weight="400" fill="var(--bs-secondary-color)" class="apexcharts-text apexcharts-yaxis-label " style="font-family: var(--bs-font-family-base);"><tspan>50%</tspan><title>50%</title></text><text x="20" y="87.25853310187658" text-anchor="end" dominant-baseline="auto" font-size="13px" font-family="var(--bs-font-family-base)" font-weight="400" fill="var(--bs-secondary-color)" class="apexcharts-text apexcharts-yaxis-label " style="font-family: var(--bs-font-family-base);"><tspan>37.5%</tspan><title>37.5%</title></text><text x="20" y="140.18373287041982" text-anchor="end" dominant-baseline="auto" font-size="13px" font-family="var(--bs-font-family-base)" font-weight="400" fill="var(--bs-secondary-color)" class="apexcharts-text apexcharts-yaxis-label " style="font-family: var(--bs-font-family-base);"><tspan>25%</tspan><title>25%</title></text><text x="20" y="193.10893263896307" text-anchor="end" dominant-baseline="auto" font-size="13px" font-family="var(--bs-font-family-base)" font-weight="400" fill="var(--bs-secondary-color)" class="apexcharts-text apexcharts-yaxis-label " style="font-family: var(--bs-font-family-base);"><tspan>12.5%</tspan><title>12.5%</title></text><text x="20" y="246.03413240750632" text-anchor="end" dominant-baseline="auto" font-size="13px" font-family="var(--bs-font-family-base)" font-weight="400" fill="var(--bs-secondary-color)" class="apexcharts-text apexcharts-yaxis-label " style="font-family: var(--bs-font-family-base);"><tspan>0%</tspan><title>0%</title></text></g></g><g class="apexcharts-inner apexcharts-graphical" transform="translate(79.41015694936117, 30)"><defs><clipPath id="gridRectMaskwiu84sywg"><rect width="419.1031232198079" height="211.70079907417298" x="0" y="0" rx="0" ry="0" opacity="1" stroke-width="0" stroke="none" stroke-dasharray="0" fill="#fff"></rect></clipPath><clipPath id="gridRectBarMaskwiu84sywg"><rect width="456.03906059265137" height="218.70079907417298" x="-18.467968686421713" y="-3.5" rx="0" ry="0" opacity="1" stroke-width="0" stroke="none" stroke-dasharray="0" fill="#fff"></rect></clipPath><clipPath id="gridRectMarkerMaskwiu84sywg"><rect width="431.1031232198079" height="223.70079907417298" x="-6" y="-6" rx="0" ry="0" opacity="1" stroke-width="0" stroke="none" stroke-dasharray="0" fill="#fff"></rect></clipPath><clipPath id="forecastMaskwiu84sywg"></clipPath><clipPath id="nonForecastMaskwiu84sywg"></clipPath></defs><line x1="0" y1="0" x2="0" y2="211.70079907417298" stroke="#b6b6b6" stroke-dasharray="3" stroke-linecap="butt" class="apexcharts-xcrosshairs" x="0" y="0" width="1" height="211.70079907417298" fill="#b1b9c4" filter="none" fill-opacity="0.9" stroke-width="1"></line><g class="apexcharts-grid"><g class="apexcharts-gridlines-horizontal"><line x1="-14.967968686421713" y1="52.925199768543244" x2="434.07109190622964" y2="52.925199768543244" stroke="var(--bs-border-color)" stroke-dasharray="8" stroke-linecap="butt" class="apexcharts-gridline"></line><line x1="-14.967968686421713" y1="105.85039953708649" x2="434.07109190622964" y2="105.85039953708649" stroke="var(--bs-border-color)" stroke-dasharray="8" stroke-linecap="butt" class="apexcharts-gridline"></line><line x1="-14.967968686421713" y1="158.77559930562973" x2="434.07109190622964" y2="158.77559930562973" stroke="var(--bs-border-color)" stroke-dasharray="8" stroke-linecap="butt" class="apexcharts-gridline"></line></g><g class="apexcharts-gridlines-vertical"></g><line x1="0" y1="211.70079907417298" x2="419.1031232198079" y2="211.70079907417298" stroke="transparent" stroke-dasharray="0" stroke-linecap="butt"></line><line x1="0" y1="1" x2="0" y2="211.70079907417298" stroke="transparent" stroke-dasharray="0" stroke-linecap="butt"></line></g><g class="apexcharts-grid-borders"><line x1="-14.967968686421713" y1="0" x2="434.07109190622964" y2="0" stroke="var(--bs-border-color)" stroke-dasharray="8" stroke-linecap="butt" class="apexcharts-gridline"></line><line x1="-14.967968686421713" y1="211.70079907417298" x2="434.07109190622964" y2="211.70079907417298" stroke="var(--bs-border-color)" stroke-dasharray="8" stroke-linecap="butt" class="apexcharts-gridline"></line></g><g class="apexcharts-bar-series apexcharts-plot-series"><g class="apexcharts-series" rel="1" seriesName="Shipment" data:realIndex="0"><path d="M -6.985052053663465 207.70179907417298 L -6.985052053663465 54.80919177780151 C -6.985052053663465 52.80919177780151 -4.985052053663465 50.80919177780151 -2.9850520536634653 50.80919177780151 L 2.9850520536634653 50.80919177780151 C 4.985052053663465 50.80919177780151 6.985052053663465 52.80919177780151 6.985052053663465 54.80919177780151 L 6.985052053663465 207.70179907417298 C 6.985052053663465 209.70179907417298 4.985052053663465 211.70179907417298 2.9850520536634653 211.70179907417298 L -2.9850520536634653 211.70179907417298 C -4.985052053663465 211.70179907417298 -6.985052053663465 209.70179907417298 -6.985052053663465 207.70179907417298 Z " fill="var(--bs-warning)" fill-opacity="1" stroke="var(--bs-warning)" stroke-opacity="1" stroke-linecap="round" stroke-width="0" stroke-dasharray="0" class="apexcharts-bar-area undefined" index="0" clip-path="url(#gridRectBarMaskwiu84sywg)" pathTo="M -6.985052053663465 207.70179907417298 L -6.985052053663465 54.80919177780151 C -6.985052053663465 52.80919177780151 -4.985052053663465 50.80919177780151 -2.9850520536634653 50.80919177780151 L 2.9850520536634653 50.80919177780151 C 4.985052053663465 50.80919177780151 6.985052053663465 52.80919177780151 6.985052053663465 54.80919177780151 L 6.985052053663465 207.70179907417298 C 6.985052053663465 209.70179907417298 4.985052053663465 211.70179907417298 2.9850520536634653 211.70179907417298 L -2.9850520536634653 211.70179907417298 C -4.985052053663465 211.70179907417298 -6.985052053663465 209.70179907417298 -6.985052053663465 207.70179907417298 Z " pathFrom="M -6.985052053663465 211.70179907417298 L -6.985052053663465 211.70179907417298 L 6.985052053663465 211.70179907417298 L 6.985052053663465 211.70179907417298 L 6.985052053663465 211.70179907417298 L 6.985052053663465 211.70179907417298 L 6.985052053663465 211.70179907417298 L -6.985052053663465 211.70179907417298 Z" cy="50.80819177780151" cx="6.985052053663465" j="0" val="38" barHeight="160.89260729637147" barWidth="13.97010410732693"></path><path d="M 39.5819616374263 207.70179907417298 L 39.5819616374263 25.17107990741729 C 39.5819616374263 23.17107990741729 41.5819616374263 21.17107990741729 43.5819616374263 21.17107990741729 L 49.552065744753236 21.17107990741729 C 51.552065744753236 21.17107990741729 53.552065744753236 23.17107990741729 53.552065744753236 25.17107990741729 L 53.552065744753236 207.70179907417298 C 53.552065744753236 209.70179907417298 51.552065744753236 211.70179907417298 49.552065744753236 211.70179907417298 L 43.5819616374263 211.70179907417298 C 41.5819616374263 211.70179907417298 39.5819616374263 209.70179907417298 39.5819616374263 207.70179907417298 Z " fill="var(--bs-warning)" fill-opacity="1" stroke="var(--bs-warning)" stroke-opacity="1" stroke-linecap="round" stroke-width="0" stroke-dasharray="0" class="apexcharts-bar-area undefined" index="0" clip-path="url(#gridRectBarMaskwiu84sywg)" pathTo="M 39.5819616374263 207.70179907417298 L 39.5819616374263 25.17107990741729 C 39.5819616374263 23.17107990741729 41.5819616374263 21.17107990741729 43.5819616374263 21.17107990741729 L 49.552065744753236 21.17107990741729 C 51.552065744753236 21.17107990741729 53.552065744753236 23.17107990741729 53.552065744753236 25.17107990741729 L 53.552065744753236 207.70179907417298 C 53.552065744753236 209.70179907417298 51.552065744753236 211.70179907417298 49.552065744753236 211.70179907417298 L 43.5819616374263 211.70179907417298 C 41.5819616374263 211.70179907417298 39.5819616374263 209.70179907417298 39.5819616374263 207.70179907417298 Z " pathFrom="M 39.5819616374263 211.70179907417298 L 39.5819616374263 211.70179907417298 L 53.552065744753236 211.70179907417298 L 53.552065744753236 211.70179907417298 L 53.552065744753236 211.70179907417298 L 53.552065744753236 211.70179907417298 L 53.552065744753236 211.70179907417298 L 39.5819616374263 211.70179907417298 Z" cy="21.17007990741729" cx="53.552065744753236" j="1" val="45" barHeight="190.5307191667557" barWidth="13.97010410732693"></path><path d="M 86.14897532851607 207.70179907417298 L 86.14897532851607 75.9792716852188 C 86.14897532851607 73.9792716852188 88.14897532851607 71.9792716852188 90.14897532851607 71.9792716852188 L 96.119079435843 71.9792716852188 C 98.119079435843 71.9792716852188 100.119079435843 73.9792716852188 100.119079435843 75.9792716852188 L 100.119079435843 207.70179907417298 C 100.119079435843 209.70179907417298 98.119079435843 211.70179907417298 96.119079435843 211.70179907417298 L 90.14897532851607 211.70179907417298 C 88.14897532851607 211.70179907417298 86.14897532851607 209.70179907417298 86.14897532851607 207.70179907417298 Z " fill="var(--bs-warning)" fill-opacity="1" stroke="var(--bs-warning)" stroke-opacity="1" stroke-linecap="round" stroke-width="0" stroke-dasharray="0" class="apexcharts-bar-area undefined" index="0" clip-path="url(#gridRectBarMaskwiu84sywg)" pathTo="M 86.14897532851607 207.70179907417298 L 86.14897532851607 75.9792716852188 C 86.14897532851607 73.9792716852188 88.14897532851607 71.9792716852188 90.14897532851607 71.9792716852188 L 96.119079435843 71.9792716852188 C 98.119079435843 71.9792716852188 100.119079435843 73.9792716852188 100.119079435843 75.9792716852188 L 100.119079435843 207.70179907417298 C 100.119079435843 209.70179907417298 98.119079435843 211.70179907417298 96.119079435843 211.70179907417298 L 90.14897532851607 211.70179907417298 C 88.14897532851607 211.70179907417298 86.14897532851607 209.70179907417298 86.14897532851607 207.70179907417298 Z " pathFrom="M 86.14897532851607 211.70179907417298 L 86.14897532851607 211.70179907417298 L 100.119079435843 211.70179907417298 L 100.119079435843 211.70179907417298 L 100.119079435843 211.70179907417298 L 100.119079435843 211.70179907417298 L 100.119079435843 211.70179907417298 L 86.14897532851607 211.70179907417298 Z" cy="71.9782716852188" cx="100.119079435843" j="2" val="33" barHeight="139.72252738895418" barWidth="13.97010410732693"></path><path d="M 132.71598901960584 207.70179907417298 L 132.71598901960584 54.80919177780151 C 132.71598901960584 52.80919177780151 134.71598901960584 50.80919177780151 136.71598901960584 50.80919177780151 L 142.68609312693278 50.80919177780151 C 144.68609312693278 50.80919177780151 146.68609312693278 52.80919177780151 146.68609312693278 54.80919177780151 L 146.68609312693278 207.70179907417298 C 146.68609312693278 209.70179907417298 144.68609312693278 211.70179907417298 142.68609312693278 211.70179907417298 L 136.71598901960584 211.70179907417298 C 134.71598901960584 211.70179907417298 132.71598901960584 209.70179907417298 132.71598901960584 207.70179907417298 Z " fill="var(--bs-warning)" fill-opacity="1" stroke="var(--bs-warning)" stroke-opacity="1" stroke-linecap="round" stroke-width="0" stroke-dasharray="0" class="apexcharts-bar-area undefined" index="0" clip-path="url(#gridRectBarMaskwiu84sywg)" pathTo="M 132.71598901960584 207.70179907417298 L 132.71598901960584 54.80919177780151 C 132.71598901960584 52.80919177780151 134.71598901960584 50.80919177780151 136.71598901960584 50.80919177780151 L 142.68609312693278 50.80919177780151 C 144.68609312693278 50.80919177780151 146.68609312693278 52.80919177780151 146.68609312693278 54.80919177780151 L 146.68609312693278 207.70179907417298 C 146.68609312693278 209.70179907417298 144.68609312693278 211.70179907417298 142.68609312693278 211.70179907417298 L 136.71598901960584 211.70179907417298 C 134.71598901960584 211.70179907417298 132.71598901960584 209.70179907417298 132.71598901960584 207.70179907417298 Z " pathFrom="M 132.71598901960584 211.70179907417298 L 132.71598901960584 211.70179907417298 L 146.68609312693278 211.70179907417298 L 146.68609312693278 211.70179907417298 L 146.68609312693278 211.70179907417298 L 146.68609312693278 211.70179907417298 L 146.68609312693278 211.70179907417298 L 132.71598901960584 211.70179907417298 Z" cy="50.80819177780151" cx="146.68609312693278" j="3" val="38" barHeight="160.89260729637147" barWidth="13.97010410732693"></path><path d="M 179.28300271069563 207.70179907417298 L 179.28300271069563 80.21328766670229 C 179.28300271069563 78.21328766670229 181.28300271069563 76.21328766670229 183.28300271069563 76.21328766670229 L 189.25310681802256 76.21328766670229 C 191.25310681802256 76.21328766670229 193.25310681802256 78.21328766670229 193.25310681802256 80.21328766670229 L 193.25310681802256 207.70179907417298 C 193.25310681802256 209.70179907417298 191.25310681802256 211.70179907417298 189.25310681802256 211.70179907417298 L 183.28300271069563 211.70179907417298 C 181.28300271069563 211.70179907417298 179.28300271069563 209.70179907417298 179.28300271069563 207.70179907417298 Z " fill="var(--bs-warning)" fill-opacity="1" stroke="var(--bs-warning)" stroke-opacity="1" stroke-linecap="round" stroke-width="0" stroke-dasharray="0" class="apexcharts-bar-area undefined" index="0" clip-path="url(#gridRectBarMaskwiu84sywg)" pathTo="M 179.28300271069563 207.70179907417298 L 179.28300271069563 80.21328766670229 C 179.28300271069563 78.21328766670229 181.28300271069563 76.21328766670229 183.28300271069563 76.21328766670229 L 189.25310681802256 76.21328766670229 C 191.25310681802256 76.21328766670229 193.25310681802256 78.21328766670229 193.25310681802256 80.21328766670229 L 193.25310681802256 207.70179907417298 C 193.25310681802256 209.70179907417298 191.25310681802256 211.70179907417298 189.25310681802256 211.70179907417298 L 183.28300271069563 211.70179907417298 C 181.28300271069563 211.70179907417298 179.28300271069563 209.70179907417298 179.28300271069563 207.70179907417298 Z " pathFrom="M 179.28300271069563 211.70179907417298 L 179.28300271069563 211.70179907417298 L 193.25310681802256 211.70179907417298 L 193.25310681802256 211.70179907417298 L 193.25310681802256 211.70179907417298 L 193.25310681802256 211.70179907417298 L 193.25310681802256 211.70179907417298 L 179.28300271069563 211.70179907417298 Z" cy="76.21228766670228" cx="193.25310681802256" j="4" val="32" barHeight="135.4885114074707" barWidth="13.97010410732693"></path><path d="M 225.85001640178538 207.70179907417298 L 225.85001640178538 4.001 C 225.85001640178538 2.0010000000000003 227.85001640178538 0.001 229.85001640178538 0.001 L 235.82012050911231 0.001 C 237.82012050911231 0.001 239.82012050911231 2.001 239.82012050911231 4.001 L 239.82012050911231 207.70179907417298 C 239.82012050911231 209.70179907417298 237.82012050911231 211.70179907417298 235.82012050911231 211.70179907417298 L 229.85001640178538 211.70179907417298 C 227.85001640178538 211.70179907417298 225.85001640178538 209.70179907417298 225.85001640178538 207.70179907417298 Z " fill="var(--bs-warning)" fill-opacity="1" stroke="var(--bs-warning)" stroke-opacity="1" stroke-linecap="round" stroke-width="0" stroke-dasharray="0" class="apexcharts-bar-area undefined" index="0" clip-path="url(#gridRectBarMaskwiu84sywg)" pathTo="M 225.85001640178538 207.70179907417298 L 225.85001640178538 4.001 C 225.85001640178538 2.0010000000000003 227.85001640178538 0.001 229.85001640178538 0.001 L 235.82012050911231 0.001 C 237.82012050911231 0.001 239.82012050911231 2.001 239.82012050911231 4.001 L 239.82012050911231 207.70179907417298 C 239.82012050911231 209.70179907417298 237.82012050911231 211.70179907417298 235.82012050911231 211.70179907417298 L 229.85001640178538 211.70179907417298 C 227.85001640178538 211.70179907417298 225.85001640178538 209.70179907417298 225.85001640178538 207.70179907417298 Z " pathFrom="M 225.85001640178538 211.70179907417298 L 225.85001640178538 211.70179907417298 L 239.82012050911231 211.70179907417298 L 239.82012050911231 211.70179907417298 L 239.82012050911231 211.70179907417298 L 239.82012050911231 211.70179907417298 L 239.82012050911231 211.70179907417298 L 225.85001640178538 211.70179907417298 Z" cy="0" cx="239.82012050911231" j="5" val="50" barHeight="211.70079907417298" barWidth="13.97010410732693"></path><path d="M 272.4170300928751 207.70179907417298 L 272.4170300928751 12.469031962966904 C 272.4170300928751 10.469031962966904 274.4170300928751 8.469031962966904 276.4170300928751 8.469031962966904 L 282.387134200202 8.469031962966904 C 284.387134200202 8.469031962966904 286.387134200202 10.469031962966904 286.387134200202 12.469031962966904 L 286.387134200202 207.70179907417298 C 286.387134200202 209.70179907417298 284.387134200202 211.70179907417298 282.387134200202 211.70179907417298 L 276.4170300928751 211.70179907417298 C 274.4170300928751 211.70179907417298 272.4170300928751 209.70179907417298 272.4170300928751 207.70179907417298 Z " fill="var(--bs-warning)" fill-opacity="1" stroke="var(--bs-warning)" stroke-opacity="1" stroke-linecap="round" stroke-width="0" stroke-dasharray="0" class="apexcharts-bar-area undefined" index="0" clip-path="url(#gridRectBarMaskwiu84sywg)" pathTo="M 272.4170300928751 207.70179907417298 L 272.4170300928751 12.469031962966904 C 272.4170300928751 10.469031962966904 274.4170300928751 8.469031962966904 276.4170300928751 8.469031962966904 L 282.387134200202 8.469031962966904 C 284.387134200202 8.469031962966904 286.387134200202 10.469031962966904 286.387134200202 12.469031962966904 L 286.387134200202 207.70179907417298 C 286.387134200202 209.70179907417298 284.387134200202 211.70179907417298 282.387134200202 211.70179907417298 L 276.4170300928751 211.70179907417298 C 274.4170300928751 211.70179907417298 272.4170300928751 209.70179907417298 272.4170300928751 207.70179907417298 Z " pathFrom="M 272.4170300928751 211.70179907417298 L 272.4170300928751 211.70179907417298 L 286.387134200202 211.70179907417298 L 286.387134200202 211.70179907417298 L 286.387134200202 211.70179907417298 L 286.387134200202 211.70179907417298 L 286.387134200202 211.70179907417298 L 272.4170300928751 211.70179907417298 Z" cy="8.468031962966904" cx="286.387134200202" j="6" val="48" barHeight="203.23276711120607" barWidth="13.97010410732693"></path><path d="M 318.98404378396486 207.70179907417298 L 318.98404378396486 46.341159814834604 C 318.98404378396486 44.341159814834604 320.98404378396486 42.341159814834604 322.98404378396486 42.341159814834604 L 328.95414789129177 42.341159814834604 C 330.95414789129177 42.341159814834604 332.95414789129177 44.341159814834604 332.95414789129177 46.341159814834604 L 332.95414789129177 207.70179907417298 C 332.95414789129177 209.70179907417298 330.95414789129177 211.70179907417298 328.95414789129177 211.70179907417298 L 322.98404378396486 211.70179907417298 C 320.98404378396486 211.70179907417298 318.98404378396486 209.70179907417298 318.98404378396486 207.70179907417298 Z " fill="var(--bs-warning)" fill-opacity="1" stroke="var(--bs-warning)" stroke-opacity="1" stroke-linecap="round" stroke-width="0" stroke-dasharray="0" class="apexcharts-bar-area undefined" index="0" clip-path="url(#gridRectBarMaskwiu84sywg)" pathTo="M 318.98404378396486 207.70179907417298 L 318.98404378396486 46.341159814834604 C 318.98404378396486 44.341159814834604 320.98404378396486 42.341159814834604 322.98404378396486 42.341159814834604 L 328.95414789129177 42.341159814834604 C 330.95414789129177 42.341159814834604 332.95414789129177 44.341159814834604 332.95414789129177 46.341159814834604 L 332.95414789129177 207.70179907417298 C 332.95414789129177 209.70179907417298 330.95414789129177 211.70179907417298 328.95414789129177 211.70179907417298 L 322.98404378396486 211.70179907417298 C 320.98404378396486 211.70179907417298 318.98404378396486 209.70179907417298 318.98404378396486 207.70179907417298 Z " pathFrom="M 318.98404378396486 211.70179907417298 L 318.98404378396486 211.70179907417298 L 332.95414789129177 211.70179907417298 L 332.95414789129177 211.70179907417298 L 332.95414789129177 211.70179907417298 L 332.95414789129177 211.70179907417298 L 332.95414789129177 211.70179907417298 L 318.98404378396486 211.70179907417298 Z" cy="42.34015981483461" cx="332.95414789129177" j="7" val="40" barHeight="169.36063925933837" barWidth="13.97010410732693"></path><path d="M 365.5510574750547 207.70179907417298 L 365.5510574750547 37.87312785186767 C 365.5510574750547 35.87312785186767 367.5510574750547 33.87312785186767 369.5510574750547 33.87312785186767 L 375.5211615823816 33.87312785186767 C 377.5211615823816 33.87312785186767 379.5211615823816 35.87312785186767 379.5211615823816 37.87312785186767 L 379.5211615823816 207.70179907417298 C 379.5211615823816 209.70179907417298 377.5211615823816 211.70179907417298 375.5211615823816 211.70179907417298 L 369.5510574750547 211.70179907417298 C 367.5510574750547 211.70179907417298 365.5510574750547 209.70179907417298 365.5510574750547 207.70179907417298 Z " fill="var(--bs-warning)" fill-opacity="1" stroke="var(--bs-warning)" stroke-opacity="1" stroke-linecap="round" stroke-width="0" stroke-dasharray="0" class="apexcharts-bar-area undefined" index="0" clip-path="url(#gridRectBarMaskwiu84sywg)" pathTo="M 365.5510574750547 207.70179907417298 L 365.5510574750547 37.87312785186767 C 365.5510574750547 35.87312785186767 367.5510574750547 33.87312785186767 369.5510574750547 33.87312785186767 L 375.5211615823816 33.87312785186767 C 377.5211615823816 33.87312785186767 379.5211615823816 35.87312785186767 379.5211615823816 37.87312785186767 L 379.5211615823816 207.70179907417298 C 379.5211615823816 209.70179907417298 377.5211615823816 211.70179907417298 375.5211615823816 211.70179907417298 L 369.5510574750547 211.70179907417298 C 367.5510574750547 211.70179907417298 365.5510574750547 209.70179907417298 365.5510574750547 207.70179907417298 Z " pathFrom="M 365.5510574750547 211.70179907417298 L 365.5510574750547 211.70179907417298 L 379.5211615823816 211.70179907417298 L 379.5211615823816 211.70179907417298 L 379.5211615823816 211.70179907417298 L 379.5211615823816 211.70179907417298 L 379.5211615823816 211.70179907417298 L 365.5510574750547 211.70179907417298 Z" cy="33.872127851867674" cx="379.5211615823816" j="8" val="42" barHeight="177.8286712223053" barWidth="13.97010410732693"></path><path d="M 412.11807116614443 207.70179907417298 L 412.11807116614443 59.04320775928496 C 412.11807116614443 57.04320775928496 414.11807116614443 55.04320775928496 416.11807116614443 55.04320775928496 L 422.08817527347134 55.04320775928496 C 424.08817527347134 55.04320775928496 426.08817527347134 57.04320775928496 426.08817527347134 59.04320775928496 L 426.08817527347134 207.70179907417298 C 426.08817527347134 209.70179907417298 424.08817527347134 211.70179907417298 422.08817527347134 211.70179907417298 L 416.11807116614443 211.70179907417298 C 414.11807116614443 211.70179907417298 412.11807116614443 209.70179907417298 412.11807116614443 207.70179907417298 Z " fill="var(--bs-warning)" fill-opacity="1" stroke="var(--bs-warning)" stroke-opacity="1" stroke-linecap="round" stroke-width="0" stroke-dasharray="0" class="apexcharts-bar-area undefined" index="0" clip-path="url(#gridRectBarMaskwiu84sywg)" pathTo="M 412.11807116614443 207.70179907417298 L 412.11807116614443 59.04320775928496 C 412.11807116614443 57.04320775928496 414.11807116614443 55.04320775928496 416.11807116614443 55.04320775928496 L 422.08817527347134 55.04320775928496 C 424.08817527347134 55.04320775928496 426.08817527347134 57.04320775928496 426.08817527347134 59.04320775928496 L 426.08817527347134 207.70179907417298 C 426.08817527347134 209.70179907417298 424.08817527347134 211.70179907417298 422.08817527347134 211.70179907417298 L 416.11807116614443 211.70179907417298 C 414.11807116614443 211.70179907417298 412.11807116614443 209.70179907417298 412.11807116614443 207.70179907417298 Z " pathFrom="M 412.11807116614443 211.70179907417298 L 412.11807116614443 211.70179907417298 L 426.08817527347134 211.70179907417298 L 426.08817527347134 211.70179907417298 L 426.08817527347134 211.70179907417298 L 426.08817527347134 211.70179907417298 L 426.08817527347134 211.70179907417298 L 412.11807116614443 211.70179907417298 Z" cy="55.04220775928496" cx="426.08817527347134" j="9" val="37" barHeight="156.658591314888" barWidth="13.97010410732693"></path><g class="apexcharts-bar-goals-markers"><g className="apexcharts-bar-goals-groups" class="apexcharts-hidden-element-shown" clip-path="url(#gridRectMarkerMaskwiu84sywg)"></g><g className="apexcharts-bar-goals-groups" class="apexcharts-hidden-element-shown" clip-path="url(#gridRectMarkerMaskwiu84sywg)"></g><g className="apexcharts-bar-goals-groups" class="apexcharts-hidden-element-shown" clip-path="url(#gridRectMarkerMaskwiu84sywg)"></g><g className="apexcharts-bar-goals-groups" class="apexcharts-hidden-element-shown" clip-path="url(#gridRectMarkerMaskwiu84sywg)"></g><g className="apexcharts-bar-goals-groups" class="apexcharts-hidden-element-shown" clip-path="url(#gridRectMarkerMaskwiu84sywg)"></g><g className="apexcharts-bar-goals-groups" class="apexcharts-hidden-element-shown" clip-path="url(#gridRectMarkerMaskwiu84sywg)"></g><g className="apexcharts-bar-goals-groups" class="apexcharts-hidden-element-shown" clip-path="url(#gridRectMarkerMaskwiu84sywg)"></g><g className="apexcharts-bar-goals-groups" class="apexcharts-hidden-element-shown" clip-path="url(#gridRectMarkerMaskwiu84sywg)"></g><g className="apexcharts-bar-goals-groups" class="apexcharts-hidden-element-shown" clip-path="url(#gridRectMarkerMaskwiu84sywg)"></g><g className="apexcharts-bar-goals-groups" class="apexcharts-hidden-element-shown" clip-path="url(#gridRectMarkerMaskwiu84sywg)"></g></g><g class="apexcharts-bar-shadows apexcharts-hidden-element-shown"></g></g></g><g class="apexcharts-line-series apexcharts-plot-series"><g class="apexcharts-series" zIndex="1" seriesName="Delivery" data:longestSeries="true" rel="1" data:realIndex="1"><path d="M 0 114.3184315000534C 16.29845479188142 114.3184315000534 30.26855889920835 93.1483515926361 46.56701369108977 93.1483515926361C 62.86546848297119 93.1483515926361 76.83557259029811 114.3184315000534 93.13402738217954 114.3184315000534C 109.43248217406095 114.3184315000534 123.40258628138788 76.21228766670228 139.7010410732693 76.21228766670228C 155.99949586515072 76.21228766670228 169.96959997247765 93.1483515926361 186.26805476435908 93.1483515926361C 202.5665095562405 93.1483515926361 216.5366136635674 25.40409588890074 232.83506845544883 25.40409588890074C 249.13352324733026 25.40409588890074 263.1036273546572 76.21228766670228 279.4020821465386 76.21228766670228C 295.70053693842 76.21228766670228 309.67064104574695 50.80819177780151 325.96909583762834 50.80819177780151C 342.2675506295098 50.80819177780151 356.2376547368367 101.61638355560302 372.53610952871816 101.61638355560302C 388.83456432059955 101.61638355560302 402.8046684279265 67.74425570373535 419.1031232198079 67.74425570373535" fill="none" fill-opacity="1" stroke="var(--bs-primary)" stroke-opacity="1" stroke-linecap="round" stroke-width="3" stroke-dasharray="0" class="apexcharts-line" index="1" clip-path="url(#gridRectBarMaskwiu84sywg)" pathTo="M 0 114.3184315000534C 16.29845479188142 114.3184315000534 30.26855889920835 93.1483515926361 46.56701369108977 93.1483515926361C 62.86546848297119 93.1483515926361 76.83557259029811 114.3184315000534 93.13402738217954 114.3184315000534C 109.43248217406095 114.3184315000534 123.40258628138788 76.21228766670228 139.7010410732693 76.21228766670228C 155.99949586515072 76.21228766670228 169.96959997247765 93.1483515926361 186.26805476435908 93.1483515926361C 202.5665095562405 93.1483515926361 216.5366136635674 25.40409588890074 232.83506845544883 25.40409588890074C 249.13352324733026 25.40409588890074 263.1036273546572 76.21228766670228 279.4020821465386 76.21228766670228C 295.70053693842 76.21228766670228 309.67064104574695 50.80819177780151 325.96909583762834 50.80819177780151C 342.2675506295098 50.80819177780151 356.2376547368367 101.61638355560302 372.53610952871816 101.61638355560302C 388.83456432059955 101.61638355560302 402.8046684279265 67.74425570373535 419.1031232198079 67.74425570373535" pathFrom="M 0 211.70079907417298 L 0 211.70079907417298 L 46.56701369108977 211.70079907417298 L 93.13402738217954 211.70079907417298 L 139.7010410732693 211.70079907417298 L 186.26805476435908 211.70079907417298 L 232.83506845544883 211.70079907417298 L 279.4020821465386 211.70079907417298 L 325.96909583762834 211.70079907417298 L 372.53610952871816 211.70079907417298 L 419.1031232198079 211.70079907417298" fill-rule="evenodd"></path><g class="apexcharts-series-markers-wrap apexcharts-hidden-element-shown" data:realIndex="1"><g class="apexcharts-series-markers" clip-path="url(#gridRectMarkerMaskwiu84sywg)"><path d="M 0, 114.3184315000534 
           m -5, 0 
           a 5,5 0 1,0 10,0 
           a 5,5 0 1,0 -10,0" fill="var(--bs-white)" fill-opacity="1" stroke="var(--bs-primary)" stroke-opacity="0.9" stroke-linecap="round" stroke-width="2" stroke-dasharray="0" cx="0" cy="114.3184315000534" shape="circle" class="apexcharts-marker wfehx5doi" rel="0" j="0" index="1" default-marker-size="5"></path><path d="M 46.56701369108977, 93.1483515926361 
           m -5, 0 
           a 5,5 0 1,0 10,0 
           a 5,5 0 1,0 -10,0" fill="var(--bs-white)" fill-opacity="1" stroke="var(--bs-primary)" stroke-opacity="0.9" stroke-linecap="round" stroke-width="2" stroke-dasharray="0" cx="46.56701369108977" cy="93.1483515926361" shape="circle" class="apexcharts-marker wzog5hmuz" rel="1" j="1" index="1" default-marker-size="5"></path></g><g class="apexcharts-series-markers" clip-path="url(#gridRectMarkerMaskwiu84sywg)"><path d="M 93.13402738217954, 114.3184315000534 
           m -5, 0 
           a 5,5 0 1,0 10,0 
           a 5,5 0 1,0 -10,0" fill="var(--bs-white)" fill-opacity="1" stroke="var(--bs-primary)" stroke-opacity="0.9" stroke-linecap="round" stroke-width="2" stroke-dasharray="0" cx="93.13402738217954" cy="114.3184315000534" shape="circle" class="apexcharts-marker wapnco23tg" rel="2" j="2" index="1" default-marker-size="5"></path></g><g class="apexcharts-series-markers" clip-path="url(#gridRectMarkerMaskwiu84sywg)"><path d="M 139.7010410732693, 76.21228766670228 
           m -5, 0 
           a 5,5 0 1,0 10,0 
           a 5,5 0 1,0 -10,0" fill="var(--bs-white)" fill-opacity="1" stroke="var(--bs-primary)" stroke-opacity="0.9" stroke-linecap="round" stroke-width="2" stroke-dasharray="0" cx="139.7010410732693" cy="76.21228766670228" shape="circle" class="apexcharts-marker wjast82kr" rel="3" j="3" index="1" default-marker-size="5"></path></g><g class="apexcharts-series-markers" clip-path="url(#gridRectMarkerMaskwiu84sywg)"><path d="M 186.26805476435908, 93.1483515926361 
           m -5, 0 
           a 5,5 0 1,0 10,0 
           a 5,5 0 1,0 -10,0" fill="var(--bs-white)" fill-opacity="1" stroke="var(--bs-primary)" stroke-opacity="0.9" stroke-linecap="round" stroke-width="2" stroke-dasharray="0" cx="186.26805476435908" cy="93.1483515926361" shape="circle" class="apexcharts-marker w047mw4ivk" rel="4" j="4" index="1" default-marker-size="5"></path></g><g class="apexcharts-series-markers" clip-path="url(#gridRectMarkerMaskwiu84sywg)"><path d="M 232.83506845544883, 25.40409588890074 
           m -5, 0 
           a 5,5 0 1,0 10,0 
           a 5,5 0 1,0 -10,0" fill="var(--bs-white)" fill-opacity="1" stroke="var(--bs-primary)" stroke-opacity="0.9" stroke-linecap="round" stroke-width="2" stroke-dasharray="0" cx="232.83506845544883" cy="25.40409588890074" shape="circle" class="apexcharts-marker wykv9ppq7" rel="5" j="5" index="1" default-marker-size="5"></path></g><g class="apexcharts-series-markers" clip-path="url(#gridRectMarkerMaskwiu84sywg)"><path d="M 279.4020821465386, 76.21228766670228 
           m -5, 0 
           a 5,5 0 1,0 10,0 
           a 5,5 0 1,0 -10,0" fill="var(--bs-white)" fill-opacity="1" stroke="var(--bs-primary)" stroke-opacity="0.9" stroke-linecap="round" stroke-width="2" stroke-dasharray="0" cx="279.4020821465386" cy="76.21228766670228" shape="circle" class="apexcharts-marker wz0uhgsdp" rel="6" j="6" index="1" default-marker-size="5"></path></g><g class="apexcharts-series-markers" clip-path="url(#gridRectMarkerMaskwiu84sywg)"><path d="M 325.96909583762834, 50.80819177780151 
           m -5, 0 
           a 5,5 0 1,0 10,0 
           a 5,5 0 1,0 -10,0" fill="var(--bs-white)" fill-opacity="1" stroke="var(--bs-primary)" stroke-opacity="0.9" stroke-linecap="round" stroke-width="2" stroke-dasharray="0" cx="325.96909583762834" cy="50.80819177780151" shape="circle" class="apexcharts-marker wi6mnkpuh" rel="7" j="7" index="1" default-marker-size="5"></path></g><g class="apexcharts-series-markers" clip-path="url(#gridRectMarkerMaskwiu84sywg)"><path d="M 372.53610952871816, 101.61638355560302 
           m -5, 0 
           a 5,5 0 1,0 10,0 
           a 5,5 0 1,0 -10,0" fill="var(--bs-white)" fill-opacity="1" stroke="var(--bs-primary)" stroke-opacity="0.9" stroke-linecap="round" stroke-width="2" stroke-dasharray="0" cx="372.53610952871816" cy="101.61638355560302" shape="circle" class="apexcharts-marker wed2o4uwj" rel="8" j="8" index="1" default-marker-size="5"></path></g><g class="apexcharts-series-markers" clip-path="url(#gridRectMarkerMaskwiu84sywg)"><path d="M 419.1031232198079, 67.74425570373535 
           m -5, 0 
           a 5,5 0 1,0 10,0 
           a 5,5 0 1,0 -10,0" fill="var(--bs-white)" fill-opacity="1" stroke="var(--bs-primary)" stroke-opacity="0.9" stroke-linecap="round" stroke-width="2" stroke-dasharray="0" cx="419.1031232198079" cy="67.74425570373535" shape="circle" class="apexcharts-marker w913njr0fh" rel="9" j="9" index="1" default-marker-size="5"></path></g></g></g><g class="apexcharts-datalabels apexcharts-hidden-element-shown" data:realIndex="0"></g><g class="apexcharts-datalabels" data:realIndex="1"></g></g><line x1="-14.967968686421713" y1="0" x2="434.07109190622964" y2="0" stroke="#b6b6b6" stroke-dasharray="0" stroke-width="1" stroke-linecap="butt" class="apexcharts-ycrosshairs"></line><line x1="-14.967968686421713" y1="0" x2="434.07109190622964" y2="0" stroke="#b6b6b6" stroke-dasharray="0" stroke-width="0" stroke-linecap="butt" class="apexcharts-ycrosshairs-hidden"></line><g class="apexcharts-xaxis" transform="translate(0, 0)"><g class="apexcharts-xaxis-texts-g" transform="translate(0, -4)"><text x="0" y="239.70079907417298" text-anchor="middle" dominant-baseline="auto" font-size="13px" font-family="var(--bs-font-family-base)" font-weight="400" fill="var(--bs-secondary-color)" class="apexcharts-text apexcharts-xaxis-label " style="font-family: var(--bs-font-family-base);"><tspan>1 Jan</tspan><title>1 Jan</title></text><text x="46.56701369108978" y="239.70079907417298" text-anchor="middle" dominant-baseline="auto" font-size="13px" font-family="var(--bs-font-family-base)" font-weight="400" fill="var(--bs-secondary-color)" class="apexcharts-text apexcharts-xaxis-label " style="font-family: var(--bs-font-family-base);"><tspan>2 Jan</tspan><title>2 Jan</title></text><text x="93.13402738217954" y="239.70079907417298" text-anchor="middle" dominant-baseline="auto" font-size="13px" font-family="var(--bs-font-family-base)" font-weight="400" fill="var(--bs-secondary-color)" class="apexcharts-text apexcharts-xaxis-label " style="font-family: var(--bs-font-family-base);"><tspan>3 Jan</tspan><title>3 Jan</title></text><text x="139.70104107326932" y="239.70079907417298" text-anchor="middle" dominant-baseline="auto" font-size="13px" font-family="var(--bs-font-family-base)" font-weight="400" fill="var(--bs-secondary-color)" class="apexcharts-text apexcharts-xaxis-label " style="font-family: var(--bs-font-family-base);"><tspan>4 Jan</tspan><title>4 Jan</title></text><text x="186.26805476435908" y="239.70079907417298" text-anchor="middle" dominant-baseline="auto" font-size="13px" font-family="var(--bs-font-family-base)" font-weight="400" fill="var(--bs-secondary-color)" class="apexcharts-text apexcharts-xaxis-label " style="font-family: var(--bs-font-family-base);"><tspan>5 Jan</tspan><title>5 Jan</title></text><text x="232.83506845544883" y="239.70079907417298" text-anchor="middle" dominant-baseline="auto" font-size="13px" font-family="var(--bs-font-family-base)" font-weight="400" fill="var(--bs-secondary-color)" class="apexcharts-text apexcharts-xaxis-label " style="font-family: var(--bs-font-family-base);"><tspan>6 Jan</tspan><title>6 Jan</title></text><text x="279.4020821465386" y="239.70079907417298" text-anchor="middle" dominant-baseline="auto" font-size="13px" font-family="var(--bs-font-family-base)" font-weight="400" fill="var(--bs-secondary-color)" class="apexcharts-text apexcharts-xaxis-label " style="font-family: var(--bs-font-family-base);"><tspan>7 Jan</tspan><title>7 Jan</title></text><text x="325.96909583762834" y="239.70079907417298" text-anchor="middle" dominant-baseline="auto" font-size="13px" font-family="var(--bs-font-family-base)" font-weight="400" fill="var(--bs-secondary-color)" class="apexcharts-text apexcharts-xaxis-label " style="font-family: var(--bs-font-family-base);"><tspan>8 Jan</tspan><title>8 Jan</title></text><text x="372.5361095287181" y="239.70079907417298" text-anchor="middle" dominant-baseline="auto" font-size="13px" font-family="var(--bs-font-family-base)" font-weight="400" fill="var(--bs-secondary-color)" class="apexcharts-text apexcharts-xaxis-label " style="font-family: var(--bs-font-family-base);"><tspan>9 Jan</tspan><title>9 Jan</title></text><text x="419.10312321980786" y="239.70079907417298" text-anchor="middle" dominant-baseline="auto" font-size="13px" font-family="var(--bs-font-family-base)" font-weight="400" fill="var(--bs-secondary-color)" class="apexcharts-text apexcharts-xaxis-label " style="font-family: var(--bs-font-family-base);"><tspan>10 Jan</tspan><title>10 Jan</title></text></g></g><g class="apexcharts-yaxis-annotations apexcharts-hidden-element-shown"></g><g class="apexcharts-xaxis-annotations apexcharts-hidden-element-shown"></g><g class="apexcharts-point-annotations apexcharts-hidden-element-shown"></g></g></svg><div class="apexcharts-tooltip apexcharts-theme-light"><div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;"></div><div class="apexcharts-tooltip-series-group apexcharts-tooltip-series-group-0" style="order: 1;"><span class="apexcharts-tooltip-marker" style="background-color: var(--bs-warning);"></span><div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;"><div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-y-label"></span><span class="apexcharts-tooltip-text-y-value"></span></div><div class="apexcharts-tooltip-goals-group"><span class="apexcharts-tooltip-text-goals-label"></span><span class="apexcharts-tooltip-text-goals-value"></span></div><div class="apexcharts-tooltip-z-group"><span class="apexcharts-tooltip-text-z-label"></span><span class="apexcharts-tooltip-text-z-value"></span></div></div></div><div class="apexcharts-tooltip-series-group apexcharts-tooltip-series-group-1" style="order: 2;"><span class="apexcharts-tooltip-marker" style="background-color: var(--bs-primary);"></span><div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;"><div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-y-label"></span><span class="apexcharts-tooltip-text-y-value"></span></div><div class="apexcharts-tooltip-goals-group"><span class="apexcharts-tooltip-text-goals-label"></span><span class="apexcharts-tooltip-text-goals-value"></span></div><div class="apexcharts-tooltip-z-group"><span class="apexcharts-tooltip-text-z-label"></span><span class="apexcharts-tooltip-text-z-value"></span></div></div></div></div><div class="apexcharts-xaxistooltip apexcharts-xaxistooltip-bottom apexcharts-theme-light"><div class="apexcharts-xaxistooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;"></div></div><div class="apexcharts-yaxistooltip apexcharts-yaxistooltip-0 apexcharts-yaxistooltip-left apexcharts-theme-light"><div class="apexcharts-yaxistooltip-text"></div></div></div></div>
      </div>
    </div>
  </div>
</div>
</div>
@endsection
