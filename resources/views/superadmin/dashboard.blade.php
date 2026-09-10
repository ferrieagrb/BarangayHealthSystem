<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white flex flex-col">
            <div class="p-4 text-2xl font-bold tracking-wider">SuperAdmin Panel</div>
            <nav class="flex-1 px-4 py-4 space-y-2">
                <a href="#" class="block px-4 py-2 rounded bg-gray-700 text-white">Dashboard</a>
                <a href="{{ route('admin.users') }}" class="block px-4 py-2 rounded hover:bg-gray-700">Manage Users</a>
                <a href="{{ route('admin.settings') }}" class="block px-4 py-2 rounded hover:bg-gray-700">System Settings</a>
            </nav>
            <div class="p-4 border-t border-gray-700">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 rounded text-red-400 hover:bg-gray-700">Logout</button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-y-auto">
            <div class="p-6 bg-white shadow flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">Welcome, {{ auth()->user()->name }}</h1>
                <span class="px-3 py-1 text-xs font-semibold text-purple-800 bg-purple-200 rounded-full">Super Admin</span>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="p-6 bg-white rounded shadow">
                        <h3 class="text-gray-500 text-sm font-medium">Role Access</h3>
                        <p class="text-2xl font-bold text-gray-800">Full System Control</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>