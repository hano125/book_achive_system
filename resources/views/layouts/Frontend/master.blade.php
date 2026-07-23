<!DOCTYPE html>

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

=========================================================
 -->
<!-- beautify ignore:start -->
<html
  lang="ar"
  class="light-style layout-menu-fixed"
  dir="rtl"
  data-theme="theme-default"
  data-assets-path="{{ asset('FrontEnd/Assets') }}/"
  data-template="vertical-menu-template-free"
>
@include("layouts.Frontend.Partials.head", ['pageTitle' => isset($title) ? $title : 'كتبي'])
  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->
@include('layouts.Frontend.sidebar')
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
          @include("layouts.Frontend.navbar")
          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
@isset($slot)
  {{ $slot }}
@else
  @yield("content")
@endisset
            </div>
            <!-- / Content -->

            @include("layouts.Frontend.Partials.footer")

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->


    @include("layouts.Frontend.Partials.scripts")
  </body>
</html>
