<?php
if (isset($_POST['getaccess'])) {
    header("Location: sup[1].php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TriMart - Promotional Products Marketplace</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>
<body class="font-poppins bg-gray-100 min-h-screen">

  <!-- Top Bar Header -->
  <header class="top-bar bg-white shadow-sm py-2">
    <div class="container mx-auto flex justify-end items-center px-4">
      <div class="flex space-x-4">
        <a href="#" class="text-red-600 hover:text-red-800 transition"> <i class="fas fa-user me-1"></i> Member Site</a>
        <a href="#" class="text-red-600 hover:text-red-800 transition"> <i class="fas fa-lock me-1"></i> Access ESP</a>
        <a href="#" class="text-red-600 hover:text-red-800 transition"> <i class="fas fa-briefcase me-1"></i> Careers</a>
      </div>
    </div>
  </header>

  <!-- Navbar -->
  <nav class="navbar bg-white border-b py-4">
    <div class="container mx-auto flex items-center justify-between px-16">
      <a class="navbar-brand" href="#">
        <img src="images/trimart.jpg" alt="TriMart" class="h-36">
      </a>
      <button class="navbar-toggler md:hidden" type="button" id="navbarToggler">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="hidden md:flex md:items-center md:space-x-4" id="navbarMenu">
        <ul class="navbar-nav flex space-x-4">
          <!-- Distributors Dropdown -->
          <li class="relative">
            <a class="nav-link text-red-600 hover:text-red-800 cursor-pointer" href="#" id="distributorsDropdown" role="button">Distributors</a>
            <ul class="dropdown-menu absolute hidden mt-2 bg-white shadow-lg rounded py-2 z-10 animate__animated animate__fadeIn" id="distributorsSubmenu">
              <li><h6 class="dropdown-header font-bold px-4 py-2">Distributors</h6></li>
              <li><a class="dropdown-item block px-4 py-2 hover:bg-red-100" href="overview.html">Overview</a></li>
              <li><a class="dropdown-item block px-4 py-2 hover:bg-red-100" href="product_marketplace.html">Product Marketplace</a></li>
              <li><a class="dropdown-item block px-4 py-2 hover:bg-red-100" href="catalog_services.html">Catalog Services</a></li>
            </ul>
          </li>

          <!-- Suppliers Dropdown -->
          <li class="relative">
            <a class="nav-link text-red-600 hover:text-red-800 cursor-pointer" href="#" id="suppliersDropdown" role="button">Suppliers</a>
            <ul class="dropdown-menu absolute hidden mt-2 bg-white shadow-lg rounded py-2 z-10 animate__animated animate__fadeIn" id="suppliersSubmenu">
              <li><h6 class="dropdown-header font-bold px-4 py-2">Suppliers</h6></li>
              <li><a class="dropdown-item block px-4 py-2 hover:bg-red-100" href="overview1.html">Overview</a></li>
              <li><a class="dropdown-item block px-4 py-2 hover:bg-red-100" href="email_marketing.html">Email Marketing</a></li>
              <li><a class="dropdown-item block px-4 py-2 hover:bg-red-100" href="credit_trimart.html">TriMart Credit Reports</a></li>
              <li><a class="dropdown-item block px-4 py-2 hover:bg-red-100" href="target_end_buyer.html">Target End-Buyers</a></li>
            </ul>
          </li>

          <!-- Shipper Dropdown -->
          <li class="relative">
            <a class="nav-link text-red-600 hover:text-red-800 cursor-pointer" href="#" id="eventsDropdown" role="button">Shipper</a>
            <ul class="dropdown-menu absolute hidden mt-2 bg-white shadow-lg rounded py-2 z-10 animate__animated animate__fadeIn" id="eventsSubmenu">
              <li><h6 class="dropdown-header font-bold px-4 py-2">Shipper</h6></li>
              <li><a class="dropdown-item block px-4 py-2 hover:bg-red-100" href="overview2.html">Overview</a></li>
              <li><a class="dropdown-item block px-4 py-2 hover:bg-red-100" href="companies.html">Companies</a></li>
            </ul>
          </li>
        </ul>
        <ul class="navbar-nav flex">
          <li class="nav-item">
            <a class="btn bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition" href="#" data-modal-target="accessModal">
              <i class="fas fa-key me-1"></i> Get Access
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

<!-- Modal for Access Overlay -->
<div id="accessModal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden" aria-hidden="true">
  <div class="modal-dialog modal-lg bg-white p-6 rounded-lg shadow-xl">
    <div class="modal-header flex justify-between items-center border-b border-gray-300 pb-4 mb-4">
      <h5 class="modal-title text-2xl font-bold text-gray-800">Get Access to TriMart</h5>
      <button type="button" class="btn-close text-gray-600 hover:text-gray-800 text-2xl transition-colors" data-modal-hide="accessModal">&times;</button>
    </div>
    <div class="modal-body p-4">
      <div id="introTab" class="tab-content">
        <div class="text-center">
          <button class="btn bg-red-600 hover:bg-red-700 text-white px-6 py-3 mb-4 rounded-lg font-semibold transition-colors shadow-md" id="go-form">
            <i class="fas fa-rocket me-2"></i>GET ACCESS
          </button>
          <h2 class="mb-4 text-2xl font-bold text-gray-800">Access the most advanced online marketplace in the promotional products industry.</h2>
          <p class="lead text-gray-600 text-lg">
            In addition to access to our online marketplace, you'll also become a member of our promotional product ecosystem which includes marketing services, sales tools, free education and networking events to help you build sustainable businesses. Complete the form to speak with a TriMart representative, or call us at 01309321179.
          </p>
        </div>
      </div>
      <div id="formTab" class="tab-content hidden">
        <form method="post">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
              <label class="form-label text-gray-700 block mb-2 font-semibold">First Name <span class="text-red-500">*</span></label>
              <input type="text" class="form-control w-full p-3 border border-gray-300 rounded bg-white text-gray-800 placeholder-gray-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-colors" placeholder="Enter your first name" required>
            </div>
            <div class="mb-4">
              <label class="form-label text-gray-700 block mb-2 font-semibold">Last Name <span class="text-red-500">*</span></label>
              <input type="text" class="form-control w-full p-3 border border-gray-300 rounded bg-white text-gray-800 placeholder-gray-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-colors" placeholder="Enter your last name" required>
            </div>
            <div class="mb-4">
              <label class="form-label text-gray-700 block mb-2 font-semibold">Email <span class="text-red-500">*</span></label>
              <input type="email" class="form-control w-full p-3 border border-gray-300 rounded bg-white text-gray-800 placeholder-gray-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-colors" placeholder="Enter your email" required>
            </div>
            <div class="mb-4">
              <label class="form-label text-gray-700 block mb-2 font-semibold">Best Number to Reach You <span class="text-red-500">*</span></label>
              <input type="text" class="form-control w-full p-3 border border-gray-300 rounded bg-white text-gray-800 placeholder-gray-500 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-colors" placeholder="Enter your phone number" required>
            </div>
            <div class="mb-4">
              <label class="form-label text-gray-700 block mb-2 font-semibold">Do you sell promotional products?</label>
              <select class="form-control w-full p-3 border border-gray-300 rounded bg-white text-gray-800 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-colors appearance-none">
                <option class="text-gray-800">Please Select</option>
                <option class="text-gray-800">Yes</option>
                <option class="text-gray-800">No</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="form-label text-gray-700 block mb-2 font-semibold">I am a... <span class="text-red-500">*</span></label>
              <select class="form-control w-full p-3 border border-gray-300 rounded bg-white text-gray-800 focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-colors appearance-none" required>
                <option class="text-gray-800">Please Select</option>
                <option class="text-gray-800">Distributor</option>
                <option class="text-gray-800">Supplier</option>
                <option class="text-gray-800">Shipper</option>
              </select>
            </div>
          </div>
          <div class="mb-4 text-gray-500 text-sm flex items-center">
            <i class="fas fa-shield-alt me-2"></i>
            <small>protected by reCAPTCHA</small>
          </div>
          <button type="submit" name="getaccess" class="btn bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors w-full shadow-md">
            <i class="fas fa-check me-2"></i>Get Access
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const goFormBtn = document.getElementById('go-form');
  const introTab = document.getElementById('introTab');
  const formTab = document.getElementById('formTab');
  
  if (goFormBtn && introTab && formTab) {
    goFormBtn.addEventListener('click', function() {
      introTab.classList.add('hidden');
      formTab.classList.remove('hidden');
    });
  }
  
  // Close modal functionality
  const closeBtn = document.querySelector('[data-modal-hide="accessModal"]');
  const modal = document.getElementById('accessModal');
  
  if (closeBtn && modal) {
    closeBtn.addEventListener('click', function() {
      modal.classList.add('hidden');
    });
  }
  
  // Close modal when clicking outside
  modal.addEventListener('click', function(e) {
    if (e.target === modal) {
      modal.classList.add('hidden');
    }
  });

});
</script>

<style>
/* Custom styles for better select dropdown appearance */
select.form-control {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.75rem center;
  background-repeat: no-repeat;
  background-size: 1rem;
  padding-right: 2.5rem;
}

select.form-control:focus {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23dc2626' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
}
</style>

  <!-- Hero Section -->
  <section class="hero bg-gray-50 py-12 text-center animate__animated animate__zoomIn">
    <div class="container mx-auto">
      <h1 class="text-4xl font-bold text-red-600 mb-4">TriMart</h1>
      <p class="text-xl text-gray-600 mb-6">Welcome to the future of promotional products.</p>
      <img src="https://via.placeholder.com/800x400?text=Hero+Image" alt="Promotional Products Hero" class="mx-auto rounded shadow-lg" style="max-height: 300px; object-fit: cover;">
    </div>
  </section>

  <!-- Content from Screenshot -->
  <section class="py-16 bg-gradient-to-r from-red-50 to-gray-100 animate__animated animate__fadeInUp">
    <div class="container mx-auto px-4">
      <div class="max-w-4xl mx-auto text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6 animate__animated animate__fadeIn">Where Promo Pros Connect and Grow</h1>
        <p class="text-xl text-gray-600 mb-8 animate__animated animate__fadeIn animate__delay-1s">TriMart is the leading marketplace where distributors and suppliers find each other, close deals, and grow their businesses — all in one place.</p>
        
        <!-- Divider -->
        <div class="border-t border-gray-300 my-12"></div>
        
        <!-- A B C D Section -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-12">
          <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 animate__animated animate__fadeInUp animate__delay-2s">
            <h3 class="text-3xl font-bold text-red-600 mb-2">A</h3>
            <p class="text-gray-600">Find Quality Suppliers</p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 animate__animated animate__fadeInUp animate__delay-2s">
            <h3 class="text-3xl font-bold text-red-600 mb-2">B</h3>
            <p class="text-gray-600">Access Premium Products</p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 animate__animated animate__fadeInUp animate__delay-2s">
            <h3 class="text-3xl font-bold text-red-600 mb-2">C</h3>
            <p class="text-gray-600">Streamline Your Operations</p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 animate__animated animate__fadeInUp animate__delay-2s">
            <h3 class="text-3xl font-bold text-red-600 mb-2">D</h3>
            <p class="text-gray-600">Grow Your Business</p>
          </div>
        </div>
        
        <!-- Divider -->
        <div class="border-t border-gray-300 my-12"></div>
        
        <!-- Best in the best with new shoes -->
        <div class="mb-12 animate__animated animate__fadeIn animate__delay-3s">
          <h2 class="text-3xl font-bold text-gray-800 mb-4">Best in the best with new shoes</h2>
          <p class="text-lg text-gray-600">Discover our latest collection of premium promotional footwear that combines style, comfort, and brand visibility.</p>
        </div>
        
        <!-- Let's Chat Section -->
        <div class="bg-red-600 text-white p-8 rounded-lg shadow-lg animate__animated animate__pulse animate__delay-4s">
          <h2 class="text-3xl font-bold mb-4">Let's chat</h2>
          <p class="text-lg mb-6">Ready to take your promotional business to the next level? Our team is here to help.</p>
          <button class="bg-white text-red-600 hover:bg-gray-100 font-bold py-3 px-8 rounded-full transition-all duration-300 transform hover:scale-105">
            Contact Us Today
          </button>
        </div>
      </div>
    </div>
  </section>

  <!-- Distributors, Suppliers, and Shippers Section -->
  <section class="py-16 bg-white animate__animated animate__fadeIn">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">How TriMart Works For You</h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">Discover how our platform serves different roles in the promotional products ecosystem</p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Distributors Card -->
        <div class="bg-gradient-to-br from-red-50 to-white p-8 rounded-xl shadow-lg border border-red-100 hover:shadow-xl transition-all duration-500 animate__animated animate__fadeInLeft">
          <div class="text-center mb-6">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-users text-red-600 text-2xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Distributors</h3>
          </div>
          <p class="text-gray-600 mb-6 text-lg">Promo Product Distributors use TriMart to source and buy bulk promotional products, enabling them to provide a wide range of items to their end customers.</p>
          <div class="text-center">
            <button class="bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-6 rounded-full transition-all duration-300 transform hover:scale-105">
              Learn More
            </button>
          </div>
        </div>
        
        <!-- Suppliers Card -->
        <div class="bg-gradient-to-br from-blue-50 to-white p-8 rounded-xl shadow-lg border border-blue-100 hover:shadow-xl transition-all duration-500 animate__animated animate__fadeInUp">
          <div class="text-center mb-6">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-industry text-blue-600 text-2xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Suppliers</h3>
          </div>
          <p class="text-gray-600 mb-6 text-lg">Promo Product Suppliers leverage TriMart to showcase their products to a vast network of distributors and their clients, expanding their market reach and boosting sales.</p>
          <div class="text-center">
            <button class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-full transition-all duration-300 transform hover:scale-105">
              Learn More
            </button>
          </div>
        </div>
        
        <!-- Shippers Card -->
        <div class="bg-gradient-to-br from-green-50 to-white p-8 rounded-xl shadow-lg border border-green-100 hover:shadow-xl transition-all duration-500 animate__animated animate__fadeInRight">
          <div class="text-center mb-6">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <i class="fas fa-shipping-fast text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Shippers</h3>
          </div>
          <p class="text-gray-600 mb-6 text-lg">Shipping partners integrate with TriMart to provide seamless logistics solutions, ensuring timely delivery of promotional products to customers worldwide with tracking and reliability.</p>
          <div class="text-center">
            <button class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-full transition-all duration-300 transform hover:scale-105">
              Learn More
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Product Marketplace and Marketing Insights Section -->
  <section class="py-16 bg-gradient-to-r from-purple-50 to-indigo-50 animate__animated animate__fadeIn">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Let TriMart Power Your Business</h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">Discover powerful tools and insights to grow your promotional products business</p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
        <!-- Product Marketplace Card -->
        <div class="bg-white p-8 rounded-xl shadow-lg border border-purple-100 hover:shadow-xl transition-all duration-500 transform hover:-translate-y-2 animate__animated animate__fadeInLeft">
          <div class="flex items-start mb-6">
            <div class="w-14 h-14 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
              <i class="fas fa-store text-purple-600 text-2xl"></i>
            </div>
            <div>
              <h3 class="text-2xl font-bold text-gray-800 mb-2">Product Marketplace</h3>
              <p class="text-gray-600 text-lg">Launch & scale your distribution business with one comprehensive platform.</p>
            </div>
          </div>
          <div class="text-left">
            <button class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 px-6 rounded-full transition-all duration-300 transform hover:scale-105 flex items-center">
              Explore ESP+
              <i class="fas fa-arrow-right ml-2"></i>
            </button>
          </div>
        </div>
        
        <!-- Marketing Insights Card -->
        <div class="bg-white p-8 rounded-xl shadow-lg border border-indigo-100 hover:shadow-xl transition-all duration-500 transform hover:-translate-y-2 animate__animated animate__fadeInRight">
          <div class="flex items-start mb-6">
            <div class="w-14 h-14 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
              <i class="fas fa-chart-line text-indigo-600 text-2xl"></i>
            </div>
            <div>
              <h3 class="text-2xl font-bold text-gray-800 mb-2">Marketing Insights</h3>
              <p class="text-gray-600 text-lg">Count on TriMart's industry-leading research to provide market insights and analysis.</p>
            </div>
          </div>
          <div class="text-left">
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-6 rounded-full transition-all duration-300 transform hover:scale-105 flex items-center">
              Explore Insights
              <i class="fas fa-arrow-right ml-2"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Industry Resource Section -->
  <section class="py-16 bg-gradient-to-br from-amber-50 to-orange-50 animate__animated animate__fadeIn">
    <div class="container mx-auto px-4">
      <div class="flex flex-col lg:flex-row items-center gap-12 max-w-6xl mx-auto">
        <!-- Left Side - Text Content -->
        <div class="lg:w-1/2 animate__animated animate__fadeInLeft">
          <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6 leading-tight">
            An industry resource built for you. And at TriMart... <span class="text-orange-600">you're family.</span>
          </h2>
          
          <div class="border-t border-amber-300 my-8"></div>
          
          <p class="text-xl text-gray-600 mb-8 leading-relaxed">
            With over 60 years in the promo industry, TriMart is still a family business that puts its member satisfaction above all else. When you become a member of TriMart, you can be sure we'll be there for you each step of the way to grow your business.
          </p>
          
          <button class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-4 px-8 rounded-full transition-all duration-300 transform hover:scale-105 flex items-center group">
            Our Story
            <i class="fas fa-arrow-right ml-3 group-hover:translate-x-1 transition-transform duration-300"></i>
          </button>
        </div>
        
        <!-- Right Side - Image -->
        <div class="lg:w-1/2 animate__animated animate__fadeInRight">
          <div class="relative">
            <div class="bg-white p-4 rounded-2xl shadow-2xl transform rotate-2 hover:rotate-0 transition-transform duration-500">
              <img 
                src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" 
                alt="TriMart Family Business - Promotional Products Team" 
                class="w-full h-96 object-cover rounded-xl shadow-lg"
              >
            </div>
            <div class="absolute -bottom-6 -left-6 bg-orange-500 text-white p-6 rounded-2xl shadow-xl animate__animated animate__pulse animate__infinite animate__slower">
              <div class="text-center">
                <div class="text-3xl font-bold">60+</div>
                <div class="text-sm font-medium">Years in Business</div>
              </div>
            </div>
            <div class="absolute -top-4 -right-4 bg-white p-4 rounded-2xl shadow-xl border border-amber-200">
              <div class="flex items-center space-x-2">
                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm font-semibold text-gray-700">Family Owned</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer Section -->
  <footer class="bg-gray-900 text-white animate__animated animate__fadeInUp">
    <div class="container mx-auto px-4 py-12">
      <!-- Main Footer Links -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
        <!-- Solutions Column -->
        <div class="animate__animated animate__fadeInUp animate__delay-1s">
          <h3 class="text-xl font-bold mb-6 text-red-400 flex items-center">
            <i class="fas fa-cogs mr-3"></i>
            Solutions
          </h3>
          <ul class="space-y-3">
            <li>
              <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                <i class="fas fa-chevron-right text-red-500 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                For Distributors
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                <i class="fas fa-chevron-right text-red-500 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                For Suppliers
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                <i class="fas fa-chevron-right text-red-500 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                For Shippers
              </a>
            </li>
          </ul>
        </div>

        <!-- Education Column -->
        <div class="animate__animated animate__fadeInUp animate__delay-2s">
          <h3 class="text-xl font-bold mb-6 text-blue-400 flex items-center">
            <i class="fas fa-graduation-cap mr-3"></i>
            Education
          </h3>
          <ul class="space-y-3">
            <li>
              <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                <i class="fas fa-chevron-right text-blue-500 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                United International University
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                <i class="fas fa-chevron-right text-blue-500 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                Market Research
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                <i class="fas fa-chevron-right text-blue-500 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                Industry News
              </a>
            </li>
          </ul>
        </div>

        <!-- About TriMart Column -->
        <div class="animate__animated animate__fadeInUp animate__delay-3s">
          <h3 class="text-xl font-bold mb-6 text-purple-400 flex items-center">
            <i class="fas fa-info-circle mr-3"></i>
            About TriMart
          </h3>
          <ul class="space-y-3">
            <li>
              <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                <i class="fas fa-chevron-right text-purple-500 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                Contact Us
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                <i class="fas fa-chevron-right text-purple-500 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                Our Story
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-300 hover:text-white transition-all duration-300 flex items-center group">
                <i class="fas fa-chevron-right text-purple-500 mr-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                Meet Our Team
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Divider -->
      <div class="border-t border-gray-700 my-8"></div>

      <!-- Bottom Footer -->
      <div class="flex flex-col md:flex-row justify-between items-center animate__animated animate__fadeIn">
        <!-- Copyright -->
        <div class="mb-4 md:mb-0">
          <p class="text-gray-400 text-sm">
            ©2025, The TriMart Marketplace®. All Rights Reserved.
          </p>
        </div>

        <!-- Policy Links -->
        <div class="flex space-x-6">
          <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300 text-sm">
            Terms of Use
          </a>
          <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300 text-sm">
            Privacy Policy
          </a>
          <a href="#" class="text-gray-400 hover:text-white transition-colors duration-300 text-sm">
            Cookie Policy
          </a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Custom JavaScript for Modal and Dropdowns -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const goFormBtn = document.querySelector("#go-form");
      const introTab = document.querySelector("#introTab");
      const formTab = document.querySelector("#formTab");
      const accessModal = document.getElementById("accessModal");
      const closeModalBtn = document.querySelector('[data-modal-hide="accessModal"]');
      const navbarToggler = document.getElementById("navbarToggler");
      const navbarMenu = document.getElementById("navbarMenu");

      // Dropdown elements
      const distributorsDropdown = document.getElementById("distributorsDropdown");
      const suppliersDropdown = document.getElementById("suppliersDropdown");
      const eventsDropdown = document.getElementById("eventsDropdown");
      const distributorsSubmenu = document.getElementById("distributorsSubmenu");
      const suppliersSubmenu = document.getElementById("suppliersSubmenu");
      const eventsSubmenu = document.getElementById("eventsSubmenu");

      // Open Modal
      document.querySelector('[data-modal-target="accessModal"]').addEventListener("click", function() {
        accessModal.classList.remove("hidden");
      });

      // Close Modal
      closeModalBtn.addEventListener("click", function() {
        accessModal.classList.add("hidden");
      });

      // Navbar Toggler
      navbarToggler.addEventListener("click", function() {
        navbarMenu.classList.toggle("hidden");
      });

      // Dropdown functionality
      if (distributorsDropdown) {
        distributorsDropdown.addEventListener("click", function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          // Close other dropdowns
          suppliersSubmenu.classList.add("hidden");
          eventsSubmenu.classList.add("hidden");
          
          // Toggle current dropdown
          distributorsSubmenu.classList.toggle("hidden");
        });
      }

      if (suppliersDropdown) {
        suppliersDropdown.addEventListener("click", function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          // Close other dropdowns
          distributorsSubmenu.classList.add("hidden");
          eventsSubmenu.classList.add("hidden");
          
          // Toggle current dropdown
          suppliersSubmenu.classList.toggle("hidden");
        });
      }

      if (eventsDropdown) {
        eventsDropdown.addEventListener("click", function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          // Close other dropdowns
          distributorsSubmenu.classList.add("hidden");
          suppliersSubmenu.classList.add("hidden");
          
          // Toggle current dropdown
          eventsSubmenu.classList.toggle("hidden");
        });
      }

      // Close dropdowns when clicking outside
      document.addEventListener("click", function() {
        distributorsSubmenu.classList.add("hidden");
        suppliersSubmenu.classList.add("hidden");
        eventsSubmenu.classList.add("hidden");
      });

      // Prevent dropdowns from closing when clicking inside them
      [distributorsSubmenu, suppliersSubmenu, eventsSubmenu].forEach(dropdown => {
        if (dropdown) {
          dropdown.addEventListener("click", function(e) {
            e.stopPropagation();
          });
        }
      });

      if (goFormBtn && introTab && formTab) {
        goFormBtn.addEventListener("click", function(e) {
          e.preventDefault();
          introTab.style.display = "none";
          formTab.style.display = "block";
          formTab.classList.add('animate__animated', 'animate__slideInUp');
        });
      }
    });
  </script>
</body>
</html>