@extends('layouts.app')
@section('title', 'Social Accounts')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Social Accounts</h1>
    <a href="{{ route('social-accounts.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">Connect Account</a>
</div>

@if($accounts->isEmpty())
    <div class="bg-white rounded-lg shadow-sm px-6 py-12 text-center text-gray-500">
        <p>No social accounts connected yet.</p>
        <a href="{{ route('social-accounts.create') }}" class="text-indigo-600 hover:underline mt-2 inline-block">Connect your first account</a>
    </div>
@else
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($accounts as $account)
            <div class="bg-white rounded-lg shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-900">{{ $account->platformLabel() }}</h3>
                    <span class="px-2 py-1 text-xs rounded-full {{ $account->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $account->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <p class="text-sm text-gray-600">{{ '@' . $account->username }}</p>
                @if($account->display_name)
                    <p class="text-sm text-gray-500">{{ $account->display_name }}</p>
                @endif
                <p class="text-xs text-gray-400 mt-2">
                    {{ $account->posts()->count() }} posts &middot;
                    Connected {{ $account->created_at->diffForHumans() }}
                </p>
                <div class="flex space-x-2 mt-4 pt-3 border-t border-gray-100">
                    <a href="{{ route('social-accounts.edit', $account) }}" class="text-sm text-indigo-600 hover:text-indigo-800">Edit</a>
                    <form method="POST" action="{{ route('social-accounts.destroy', $account) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800" onclick="return confirm('Remove this account? Associated posts will also be deleted.')">Remove</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
