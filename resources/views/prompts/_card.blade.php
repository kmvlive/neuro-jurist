<a href="{{ route('chat.show', ['prompt' => $prompt->key]) }}" 
   class="group block p-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg hover:border-blue-300 dark:hover:border-blue-500 hover:shadow-md transition">
    <div class="flex items-start gap-3">
        <span class="text-2xl flex-shrink-0">{{ $prompt->icon }}</span>
        <div class="flex-1 min-w-0">
            <h4 class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                {{ $prompt->title }}
            </h4>
            @if($prompt->text)
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                    {{ Str::limit($prompt->text, 80) }}
                </p>
            @endif
        </div>
        <span class="text-gray-400 group-hover:text-blue-500 transition flex-shrink-0">→</span>
    </div>
</a>
