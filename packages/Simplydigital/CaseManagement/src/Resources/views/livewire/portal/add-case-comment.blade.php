<section class="space-y-5">
    <div>
        <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Conversation</h2>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Only customer-visible updates are shown here.</p>
    </div>

    @if ($comments->isEmpty())
        <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-8 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
            No updates have been shared yet.
        </div>
    @else
        <div class="space-y-4">
            @foreach ($comments as $comment)
                <article wire:key="portal-comment-{{ $comment->id }}" class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $comment->user?->name ?? $comment->external_user ?? 'Support team' }}</h3>
                        <time class="text-xs text-zinc-500 dark:text-zinc-400" datetime="{{ $comment->created_at->toISOString() }}">{{ $comment->created_at->format('M j, Y \a\t g:i a') }}</time>
                    </div>
                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-zinc-700 dark:text-zinc-300">{{ $comment->description }}</p>
                </article>
            @endforeach
        </div>
    @endif

    @if (\Simplydigital\CaseManagement\Enums\CaseState::isOpen($case->state->value))
        <form wire:submit="submit" class="space-y-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:field>
                <flux:label>Add a reply</flux:label>
                <flux:textarea wire:model="comment" rows="4" maxlength="10000" placeholder="Write a customer-visible update..." />
                <flux:error name="comment" />
            </flux:field>
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" icon="paper-airplane" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Send reply</span>
                    <span wire:loading wire:target="submit">Sending…</span>
                </flux:button>
            </div>
        </form>
    @else
        <p class="rounded-xl bg-zinc-100 px-4 py-3 text-sm text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
            This case is closed. You can still read the conversation, but replies are no longer accepted.
        </p>
    @endif
</section>
