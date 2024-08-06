<x-filament-panels::page fullHeight="true">
    <div
        class="flex h-full max-w-4xl flex-col justify-between"
        x-data="{}"
        x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('filament-openai-assistant', 'ercogx/filament-openai-assistant'))]"
    >
        <div
            class="fi-fo-component-ctn grid grid-cols-[--cols-default] gap-6 lg:grid-cols-[--cols-lg]"
            style="
                --cols-default: repeat(1, minmax(0, 1fr));
                --cols-lg: repeat(3, minmax(0, 1fr));
            "
        >
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="selectedAssistant">
                    @foreach ($assistants as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input.select
                    wire:model.live="selectedThread"
                    wire:key="{{ $selectedAssistant }}"
                >
                    @foreach ($threads as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>

            @if ($selectedThread !== 'new-thread')
                <x-filament::modal id="rename-thread">
                    <x-slot name="trigger">
                        <x-filament::button class="w-full">
                            {{ __('filament-openai-assistant::assistant-page.rename_current_thread') }}
                        </x-filament::button>
                    </x-slot>

                    <form wire:submit="renameCurrentThread">
                        <x-filament::input.wrapper
                            :valid="! $errors->has('selectedThreadName')"
                        >
                            <x-filament::input
                                type="text"
                                wire:model="selectedThreadName"
                                required
                            />
                        </x-filament::input.wrapper>

                        @error('selectedThreadName')
                            <x-filament-forms::field-wrapper.error-message>
                                {{ $message }}
                            </x-filament-forms::field-wrapper.error-message>
                        @enderror

                        <x-filament::button type="submit" class="mt-3 w-full">
                            {{ __('filament-openai-assistant::assistant-page.rename') }}
                        </x-filament::button>
                    </form>
                </x-filament::modal>
            @endif
        </div>

        <div
            class="assistant-ai-messages mt-4 flex flex-grow overflow-y-auto"
            x-data="{
                isScrollable: false,
                scroll() {
                    $el.scrollTo(0, $el.scrollHeight)
                    this.isScrollable = $el.offsetHeight < $el.scrollHeight
                },
            }"
            x-intersect="scroll()"
            x-on:chat-updated.window="$nextTick(() => scroll())"
            :style="isScrollable && { 'padding-right': '1rem' }"
        >
            <div class="mt-auto flex w-full flex-col justify-end">
                @if ($hasMore)
                    <div class="mb-4 flex w-full justify-center">
                        <x-filament::button
                            wire:click="loadMoreMessage"
                            class="mt-3"
                        >
                            {{ __('filament-openai-assistant::assistant-page.load_more') }}
                        </x-filament::button>
                    </div>
                @endif

                @foreach ($messages as $message)
                    <div
                        @class([
                            'mb-4 max-w-xl rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10',
                            'self-end' => $message['role'] === 'user',
                            'self-start' => $message['role'] === 'assistant',
                        ])
                    >
                        <span
                            class="text-sm font-semibold leading-6 text-gray-950 dark:text-white"
                        >
                            {{ ucfirst($message['role']) }}
                        </span>
                        <div
                            class="assistant-ai-message text-sm leading-6 text-gray-950 dark:text-white"
                        >
                            <div class="reset-styles">
                                {!! (new \Parsedown())->text($message['text']) !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mb-2" wire:loading>
            <div class="loader-ioa"></div>
        </div>

        <form wire:submit="sendMessage" class="flex gap-x-4">
            <x-filament::input.wrapper class="flex-grow">
                <textarea
                    @if (\Filament\Support\Facades\FilamentView::hasSpaMode())
                        ax-load="visible"
                    @else
                        ax-load
                    @endif
                    ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('textarea', 'filament/forms') }}"
                    x-data="textareaFormComponent({ initialHeight: @js(2 * 1.5 + 0.75) })"
                    rows="2"
                    class="block w-full border-none bg-transparent px-3 py-1.5 text-base text-gray-950 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] sm:text-sm sm:leading-6 dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)]"
                    placeholder="{{ __('filament-openai-assistant::assistant-page.type_message') }}"
                    wire:model="message"
                    required
                ></textarea>
            </x-filament::input.wrapper>

            <x-filament::button
                icon="heroicon-o-paper-airplane"
                icon-position="after"
                type="submit"
            >
                {{ __('filament-openai-assistant::assistant-page.send') }}
            </x-filament::button>
        </form>
    </div>
</x-filament-panels::page>
