@extends('layouts.master')
@section('body')
    <div class="m-6">
        <div class="flex items-baseline px-6 pb-4">
            <h1 class="page-header mb-0 px-0 mr-4">Courses</h1>
            @can('create', App\Course::class)
            <button class="btn btn-magenta is-sm shadow-inset" @click="$modal.show('create-courses-modal')">
                New
            </button>
            @include('staff.courses.modals.create', [
                'modalName' => 'create-college-modal',
                'courseTypes' => $courseTypes,
            ])
            @endcan
        </div>
        @can('update', App\Course::class)
        @include('staff.courses.modals.edit', [
            'modalName' => 'edit-course-modal',
            'courseTypes' => $courseTypes,
        ])
        @endcan
        <div>
            @foreach ($courses as $course)
                <div class="relative p-6 page-card shadow hover:bg-gray-100 hover:shadow-lg mb-2 leading-none">
                    <div class="flex items-center mb-2">
                        <span class="px-2 py-1 rounded text-sm uppercase text-white bg-black font-bold font-mono">
                            {{ $course->type }}
                        </span>
                        <span class="font-bold text-gray-700 ml-2">{{ $course->code }}</span>
                    </div>
                    <h3 class="font-bold text-lg capitalize mb-4">{{ $course->name }}</h3>
                    @if($latestRevision = $course->revisions->shift())
                    <div class="leading-none my-4">
                        <div class="flex items-center mb-1">
                            <h5 class="font-medium">Latest Revision w.e.f <strong>{{ $latestRevision->revised_at->format('M, Y') }}</strong></h5>
                            <form method="POST" action="{{ route('staff.courses.revisions.destroy', [
                                'course' => $course,
                                'revision' => $latestRevision
                            ]) }}" class="ml-2">
                                @csrf_token @method('DELETE')
                                <button type="submit" class="p-2 text-sm text-gray-700 hover:text-red-600 hover:bg-gray-300 rounded">
                                    <feather-icon name="trash-2" class="h-current"></feather-icon>
                                </button>
                            </form>
                        </div>
                        <div class="flex flex-wrap -mx-2 -my-1">
                            @foreach ($latestRevision->attachments as $attachment)
                            <div class="inline-flex items-center p-2 rounded border hover:bg-gray-300 text-gray-600 mx-2 my-1">
                                <a href="{{ route('staff.attachments.show', $attachment) }}" target="__blank" class="inline-flex items-center mr-1">
                                    <feather-icon name="paperclip" class="h-4 mr-2" stroke-width="2">View Attachment</feather-icon>
                                    <span>{{ $attachment->original_name }}</span>
                                </a>
                                @can('delete', $attachment)
                                <button type="submit" form="remove-attachment" formaction="{{ route('staff.attachments.destroy', $attachment) }}"
                                    class="p-1 rounded hover:bg-red-500 hover:text-white">
                                    <feather-icon name="x" class="h-4" stroke-width="2">Delete Attachment</feather-icon>
                                </button>
                                @endcan
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <details class="bg-gray-100 rounded-t border overflow-hidden mb-1">
                        <summary class="p-2 bg-gray-200 cursor-pointer outline-none">
                            <span class="mr-2">Add New Revision</span>
                        </summary>
                        <form action="{{ route('staff.courses.revisions.store', $course) }}"
                            method="POST" class="px-4"
                            enctype="multipart/form-data">
                            @csrf_token
                            <div class="flex items-end mb-2">
                                <div class="flex-1 mr-1">
                                    <label for="revision_date" class="w-full form-label mb-1">
                                        Revision Date <span class="text-red-600">*</span>
                                    </label>
                                    <input type="date" name="revised_at" id="revision_date" class="w-full form-input">
                                </div>
                                <v-file-input id="revision_attachments" name="attachments[]"
                                    accept="application/pdf, image/*"
                                    class="flex-1 ml-1"
                                    placeholder="Select multiple files" multiple required>
                                    <template v-slot="{ label }">
                                        <span class="w-full form-label mb-1">
                                            Upload Syllabus <span class="text-red-600">*</span>
                                        </span>
                                        <div class="w-full form-input inline-flex items-center" tabindex="0">
                                            <feather-icon name="upload" class="h-4 mr-2 text-gray-700 flex-shrink-0"></feather-icon>
                                            @{{ label }}
                                        </div>
                                    </template>
                                </v-file-input>
                                <div class="ml-1 flex-shrink-0">
                                    <button type="submit" class="btn btn-magenta px-6">Add</button>
                                </div>
                            </div>
                        </form>
                    </details>
                    <details class="bg-gray-100 rounded-t border overflow-hidden">
                        <summary class="p-2 bg-gray-200 cursor-pointer outline-none">
                            <span class="mr-2">Older Revisions</span>
                        </summary>
                        <ul class="p-4 list-disc ml-4">
                            @forelse($course->revisions as $courseRevision)
                            <li>
                                <div class="flex items-center mb-1">
                                    <h5 class="font-medium">Revision w.e.f <strong>{{ $courseRevision->revised_at->format('M, Y') }}</strong></h5>
                                    <form method="POST" action="{{ route('staff.courses.revisions.destroy', ([
                                        'course' => $course,
                                        'revision' => $courseRevision
                                        ])) }}"
                                        class="ml-2">
                                        @csrf_token @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-700 hover:text-red-600 hover:bg-gray-300 rounded">
                                            <feather-icon name="trash-2" class="h-current"></feather-icon>
                                        </button>
                                    </form>
                                </div>
                                <div class="flex flex-wrap -mx-2">
                                    @foreach ($courseRevision->attachments as $attachment)
                                        <div class="inline-flex items-center p-2 rounded border hover:bg-gray-300 text-gray-600 mx-2 my-1">
                                            <a href="{{ route('staff.attachments.show', $attachment) }}" target="__blank" class="inline-flex items-center mr-1">
                                                <feather-icon name="paperclip" class="h-4 mr-2" stroke-width="2">View Attachment</feather-icon>
                                                <span>{{ $attachment->original_name }}</span>
                                            </a>
                                            @can('delete', $attachment)
                                            <button type="submit" form="remove-attachment" formaction="{{ route('staff.attachments.destroy', $attachment) }}"
                                                class="p-1 rounded hover:bg-red-500 hover:text-white">
                                                <feather-icon name="x" class="h-4" stroke-width="2">Delete Attachment</feather-icon>
                                            </button>
                                            @endcan
                                        </div>
                                    @endforeach
                                </div>
                            </li>
                            @empty
                            <p class="p-4 text-gray-600 font-bold">No older Revisions.</p>
                            @endforelse
                        </ul>
                    </details>
                    <div class="absolute top-0 right-0 mt-4 mr-4 flex items-center">
                        @can('update', App\Course::class)
                        <button class="p-1 hover:text-blue-500 mr-2"
                        @click.prevent="$modal.show('edit-course-modal', {
                            course: {{ $course->toJson() }}
                        })">
                            <feather-icon name="edit" class="h-current">Edit</feather-icon>
                        </button>
                        @endcan
                        @can('delete', App\Course::class)
                        <form action="{{ route('staff.courses.destroy', $course) }}" method="POST"
                            onsubmit="return confirm('Do you really want to delete course?');">
                            @csrf_token
                            @method('DELETE')
                            <button type="submit" class="p-1 hover:text-red-700">
                                <feather-icon name="trash-2" class="h-current">Delete</feather-icon>
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection