<div class="page-card border-b mb-4 pt-6 pb-0 overflow-hidden">
    <div class="px-6">
        <div class="flex items-center mb-3">
            <span class="px-2 py-1 rounded text-xs uppercase text-white {{
                $letter->type == 'Bill'
                ? 'bg-blue-600'
                : ($letter->type == 'Notesheet' ? 'bg-teal-600' : 'bg-gray-800')
            }} mr-2 font-bold">
                {{ $letter->type }}
            </span>
            <h5 class="mr-12 text-gray-700 font-bold">{{$letter->serial_no}}</h5>
            <h5 class="mr-12 text-gray-700 font-bold">{{ $letter->date->format('M d, Y') }}</h5>
            <div class="flex items-center text-gray-700">
                {{ $letter->sender->name }}
                <feather-icon name="arrow-up-right"
                stroke-width="3"
                class="h-current text-green-600 mx-2">Sent to</feather-icon>
                {{ $letter->recipient }}
            </div>
            <div class="ml-auto flex">
                @can('update', $letter)
                <a href="/outgoing-letters/{{$letter->id}}/edit"
                    class="p-1 text-gray-500 text-blue-600 hover:bg-gray-200 rounded mr-3" title="Edit">
                    <feather-icon name="edit-3" stroke-width="2.5" class="h-current">Edit</feather-icon>
                </a>
                @endcan
                @can('delete', $letter)
                <form method="POST" action="/outgoing-letters/{{$letter->id}}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1 hover:bg-gray-200 text-red-700 rounded">
                        <feather-icon name="trash-2" stroke-width="2.5" class="h-current">Delete</feather-icon>
                    </button>
                </form>
                @endcan
            </div>
        </div>
        <h4 class="text-xl font-bold mb-3">{{ $letter->subject ?? 'Subject of Letter' }}</h4>
        @isset($letter->amount)
        <div class="flex items-end">
            <p class="w-2/3 text-black-50">{{ $letter->description }}</p>
            <div class="flex-1 px-4 text-xl font-bold text-right">
                &#x20B9;{{ substr(number_format($letter->amount, 2), 0, -2) }}
                <span class="text-sm">
                    {{ substr(number_format($letter->amount, 2), -2, 2) }}
                </span>
            </div>
        </div>
        @else
            <p class="text-black-50">{{ $letter->description }}</p>
        @endisset
        <div class="flex flex-wrap -mx-2 my-3">
            @foreach ($letter->attachments as $attachment)
                <span class="p-2 rounded border hover:bg-gray-300 text-gray-600 m-2">
                    <a href="/attachments/{{ $attachment->id }}" target="__blank" class="inline-flex items-center mr-1">
                        <feather-icon name="paperclip" class="h-4 mr-2" stroke-width="2">View Attachment</feather-icon>
                        <span>{{ $attachment->original_name }}</span>
                    </a>
                </span>
            @endforeach
        </div>
    </div>
    <v-tabbed-pane default-tab="remarks">
        <template v-slot:tabs="{ select, isActive }">
            <div class="flex px-6 border-b">
                @can('viewAny', App\Remark::class)
                <button class="inline-flex items-center border border-b-0 rounded-t px-3 py-2 mx-1"
                    style="margin-bottom: -1px;" role="tab"
                    :class="{
                        'bg-gray-100': isActive('remarks'),
                        'bg-gray-300': !isActive('remarks'),
                    }"
                    @click="select('remarks')">
                    <feather-icon name="list" class="h-current mr-1">Remarks</feather-icon>
                    Remarks
                    <span class="ml-3 py-1 px-2 inline-flex items-center rounded-full bg-gray-500 text-xs text-black">{{ $letter->remarks->count() }}</span>
                </button>
                @endcan
                @can('viewAny', App\LetterReminder::class)
                <button class="inline-flex items-center border border-b-0 rounded-t px-3 py-2 mx-1"
                    style="margin-bottom: -1px;" role="tab"
                    :class="{
                        'bg-gray-100': isActive('reminders'),
                        'bg-gray-300': !isActive('reminders'),
                    }"
                    @click="select('reminders')">
                    <feather-icon name="bell" class="h-current mr-1">Reminders</feather-icon>
                    Reminders
                    <span class="ml-3 py-1 px-2 inline-flex items-center rounded-full bg-gray-500 text-xs text-black">{{ $letter->reminders->count() }}</span>
                </button>
                @endcan
            </div>
        </template>
        <template v-slot:default="{ isActive }">
            @can('viewAny', App\Remark::class)
            <div v-show="isActive('remarks')">
                @include('outgoing_letters.partials.remarks')
            </div>
            @endcan
            @can('viewAny', App\LetterReminder::class)
            <div v-show="isActive('reminders')">
                @include('outgoing_letters.partials.reminders')
            </div>
            @endcan
        </template>
    </v-tabbed-pane>
</div>
