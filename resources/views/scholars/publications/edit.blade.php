@extends('layouts.scholars')
@section('body')
    <div class="page-card max-w-xl mx-auto my-4">
        <div class="page-header flex items-baseline">
            <h2 class="mr-6">Update Publication</h2>
        </div>
        <form action="{{ route('scholars.profile.publication.update' , $publication )}}" method="post" class="px-6">
            @csrf_token
            @method('PATCH')
            @include('scholars.partials.academic_details_edit', [
                'paper' => $publication
            ])
        </form>
    </div>
@endsection