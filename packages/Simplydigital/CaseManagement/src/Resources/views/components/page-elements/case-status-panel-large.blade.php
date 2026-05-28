@props([
    'cases' => null,
])


<!-- Status Summary Panel -->
<div class="w-full rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
  
  <!-- Header -->
  <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
      <h2 class="text-xl font-semibold text-gray-900">Case Status Overview</h2>
      <p class="text-sm text-gray-500">
        Monitor response SLA and case handling status
      </p>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-2">
      <button class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
        All Cases
      </button>
      <button class="rounded-lg bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
        Expired
      </button>
      <button class="rounded-lg bg-yellow-50 px-4 py-2 text-sm font-medium text-yellow-700 hover:bg-yellow-100">
        Warning
      </button>
      <button class="rounded-lg bg-green-50 px-4 py-2 text-sm font-medium text-green-700 hover:bg-green-100">
        Resolved
      </button>
      <button class="rounded-lg bg-green-50 px-4 py-2 text-sm font-medium text-green-700 hover:bg-green-100">
        Paused
      </button>      
    </div>
  </div>

  <!-- Status Cards -->
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

    <!-- Expired -->
    <div class="rounded-xl border border-red-100 bg-red-50 p-4">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-sm font-medium text-red-700">Expired</p>
          <h3 class="mt-2 text-3xl font-bold text-red-900">14</h3>
          <p class="mt-1 text-xs text-red-600">
            SLA breached
          </p>
        </div>

        <!-- Flag -->
        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100">
          🚩
        </div>
      </div>

      <div class="mt-4 h-2 rounded-full bg-red-100">
        <div class="h-2 w-3/4 rounded-full bg-red-500"></div>
      </div>
    </div>

    <!-- Warning -->
    <div class="rounded-xl border border-yellow-100 bg-yellow-50 p-4">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-sm font-medium text-yellow-700">
            Close to Expiry
          </p>
          <h3 class="mt-2 text-3xl font-bold text-yellow-900">23</h3>
          <p class="mt-1 text-xs text-yellow-600">
            SLA nearing limit
          </p>
        </div>

        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-100">
          ⚠️
        </div>
      </div>

      <div class="mt-4 h-2 rounded-full bg-yellow-100">
        <div class="h-2 w-2/3 rounded-full bg-yellow-500"></div>
      </div>
    </div>

    <!-- Unassigned -->
    <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-sm font-medium text-blue-700">
            Unassigned
          </p>
          <h3 class="mt-2 text-3xl font-bold text-blue-900">11</h3>
          <p class="mt-1 text-xs text-blue-600">
            Awaiting owner
          </p>
        </div>

        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
          🏳️
        </div>
      </div>

      <div class="mt-4 h-2 rounded-full bg-blue-100">
        <div class="h-2 w-1/2 rounded-full bg-blue-500"></div>
      </div>
    </div>

    <!-- No Action -->
    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-sm font-medium text-gray-700">
            No Action
          </p>
          <h3 class="mt-2 text-3xl font-bold text-gray-900">7</h3>
          <p class="mt-1 text-xs text-gray-500">
            Pending update
          </p>
        </div>

        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-200">
          ⏸️
        </div>
      </div>

      <div class="mt-4 h-2 rounded-full bg-gray-200">
        <div class="h-2 w-1/3 rounded-full bg-gray-500"></div>
      </div>
    </div>

    <!-- Resolved -->
    <div class="rounded-xl border border-green-100 bg-green-50 p-4">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-sm font-medium text-green-700">
            Resolved
          </p>
          <h3 class="mt-2 text-3xl font-bold text-green-900">128</h3>
          <p class="mt-1 text-xs text-green-600">
            Successfully closed
          </p>
        </div>

        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
          ✅
        </div>
      </div>

      <div class="mt-4 h-2 rounded-full bg-green-100">
        <div class="h-2 w-5/6 rounded-full bg-green-500"></div>
      </div>
    </div>

  </div>
</div>