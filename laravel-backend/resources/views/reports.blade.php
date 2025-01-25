@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="container mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Reports</h1>
    </div>

    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="table-auto w-full text-left border-collapse">
            <thead class="bg-gray-100 text-gray-700 text-sm font-semibold">
                <tr>
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">Description</th>
                    <th class="px-6 py-4">Created At</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm divide-y divide-gray-200">
                @forelse ($reports as $report)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">{{ $report['name'] }}</td>
                    <td class="px-6 py-4">{{ $report['description'] }}</td>
                    <td class="px-6 py-4">{{ $report['created_at'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center px-6 py-4 text-gray-500">No reports found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
