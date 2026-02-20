{{--
    resources/views/filament/components/image-insert-widget.blade.php
    JS logic didefinisikan sekali via @pushOnce sebagai fungsi Alpine bernama,
    sehingga x-data hanya berisi nama fungsi kecil dan aman dari formatter.
--}}

@pushOnce('scripts')
    <script>
        function imageInsertWidget(uploadUrl) {
            return {
                uploadUrl: uploadUrl,
                open: false,
                file: null,
                previewUrl: null,
                width: '50',
                widthUnit: '%',
                align: 'center',
                uploading: false,
                uploadedUrl: null,
                error: null,
                dragOver: false,

                handleFile(event) {
                    const f = event.target.files[0];
                    if (f) this.selectFile(f);
                },

                handleDrop(event) {
                    this.dragOver = false;
                    const f = event.dataTransfer.files[0];
                    if (f && f.type.startsWith('image/')) this.selectFile(f);
                },

                selectFile(f) {
                    this.file = f;
                    this.previewUrl = URL.createObjectURL(f);
                    this.uploadedUrl = null;
                    this.error = null;
                    this.uploadFile(f);
                },

                async uploadFile(f) {
                    this.uploading = true;
                    const fd = new FormData();
                    fd.append('image', f);
                    fd.append('_token', document.querySelector('meta[name=csrf-token]').getAttribute('content'));
                    try {
                        const res = await fetch(this.uploadUrl, {
                            method: 'POST',
                            body: fd
                        });
                        const data = await res.json();
                        if (data.url) {
                            this.uploadedUrl = data.url;
                        } else {
                            this.error = data.message || data.error || 'Upload gagal.';
                        }
                    } catch (e) {
                        this.error = 'Koneksi error: ' + e.message;
                    }
                    this.uploading = false;
                },

                getImageStyle() {
                    const w = this.widthUnit === '%' ? this.width + '%' : this.width + 'px';
                    let s = 'width:' + w + ';max-width:100%;';
                    if (this.align === 'center') s += 'display:block;margin:8px auto;';
                    else if (this.align === 'left') s += 'float:left;margin:0 14px 8px 0;';
                    else if (this.align === 'right') s += 'float:right;margin:0 0 8px 14px;';
                    else s += 'display:block;margin:4px 0;';
                    return s;
                },

                insertImage() {
                    if (!this.uploadedUrl) return;

                    const style = this.getImageStyle();

                    // Filament v4 uses TipTap — find the closest .fi-fo-rich-editor
                    // element above this widget and call its Alpine getEditor() method.
                    const widgetTop = this.$el.getBoundingClientRect().top;
                    const allWrappers = Array.from(document.querySelectorAll('.fi-fo-rich-editor'));
                    let targetWrapper = null;
                    let closestDist = Infinity;
                    for (const w of allWrappers) {
                        const rect = w.getBoundingClientRect();
                        const dist = widgetTop - rect.bottom;
                        if (dist >= -5 && dist < closestDist) {
                            closestDist = dist;
                            targetWrapper = w;
                        }
                    }

                    if (!targetWrapper) {
                        this.error = 'Editor tidak ditemukan di atas widget ini.';
                        return;
                    }

                    // Get the Alpine data from the inner div that has x-data
                    const alpineEl = targetWrapper.querySelector('[x-data]') || targetWrapper;
                    const alpineData = Alpine.$data(alpineEl);
                    const tiptap = alpineData && typeof alpineData.getEditor === 'function' ?
                        alpineData.getEditor() :
                        null;

                    if (tiptap) {
                        const html = '<img src="' + this.uploadedUrl + '" style="' + style + '" alt="">';
                        tiptap.chain().focus().insertContent(html, {
                            parseOptions: {
                                preserveWhitespace: false
                            }
                        }).run();
                        this.reset();
                        this.open = false;
                    } else {
                        this.error = 'TipTap editor instance tidak ditemukan.';
                    }
                },

                reset() {
                    this.file = null;
                    this.previewUrl = null;
                    this.uploadedUrl = null;
                    this.error = null;
                    if (this.$refs.fileInput) this.$refs.fileInput.value = '';
                },

                previewAlignClass() {
                    if (this.align === 'center') return 'justify-center';
                    if (this.align === 'right') return 'justify-end';
                    return 'justify-start';
                }
            };
        }
    </script>
@endPushOnce

<div x-data="imageInsertWidget('{{ route('question.image.upload') }}')" class="mt-1 mb-3">

    {{-- ───── Toggle button ───── --}}
    <button type="button" @click="open = !open"
        class="inline-flex items-center gap-x-1.5 rounded-lg px-3 py-1.5 text-sm font-medium
               text-gray-700 bg-white border border-gray-300
               hover:bg-gray-50 hover:border-gray-400
               dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700
               focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="w-4 h-4">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
        </svg>
        <span x-text="open ? 'Tutup Sisipkan Gambar' : 'Sisipkan Gambar ke Editor'"></span>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
            class="w-3 h-3 opacity-60 transition-transform duration-200" :class="open ? '-rotate-180' : ''">
            <path fill-rule="evenodd" clip-rule="evenodd"
                d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" />
        </svg>
    </button>

    {{-- ───── Expandable panel ───── --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="mt-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 overflow-hidden">
        <div class="p-4 space-y-4">

            {{-- ── Drop zone ── --}}
            <div x-show="!previewUrl" @click="$refs.fileInput.click()" @dragover.prevent="dragOver = true"
                @dragleave.prevent="dragOver = false" @drop.prevent="handleDrop($event)"
                :class="dragOver
                    ?
                    'border-primary-400 bg-primary-50 dark:bg-primary-900/30' :
                    'border-gray-300 dark:border-gray-600 hover:border-primary-400 dark:hover:border-primary-500 bg-white dark:bg-gray-800'"
                class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed p-8 cursor-pointer transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-10 h-10 mb-2"
                    :class="dragOver ? 'text-primary-400' : 'text-gray-300 dark:text-gray-600'">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                </svg>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    <span class="text-primary-600 dark:text-primary-400">Klik untuk pilih</span> atau seret gambar ke
                    sini
                </p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">PNG, JPG, GIF, WebP — maks. 5 MB</p>
                <input type="file" x-ref="fileInput" accept="image/*" @change="handleFile($event)" class="hidden">
            </div>

            {{-- ── Preview + controls ── --}}
            <div x-show="previewUrl" class="space-y-4">

                {{-- Uploading spinner --}}
                <div x-show="uploading" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <svg class="animate-spin h-4 w-4 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    Mengunggah gambar…
                </div>

                {{-- Error message --}}
                <div x-show="error"
                    class="flex items-start gap-2 rounded-lg bg-danger-50 dark:bg-danger-900/30 border border-danger-200 dark:border-danger-700 px-3 py-2 text-sm text-danger-700 dark:text-danger-400">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="w-4 h-4 flex-shrink-0 mt-0.5">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
                            clip-rule="evenodd" />
                    </svg>
                    <span x-text="error"></span>
                </div>

                {{-- Live preview --}}
                <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3">
                    <p class="text-xs text-gray-400 dark:text-gray-500 mb-2 uppercase tracking-wide font-medium">
                        Pratinjau</p>
                    <div class="flex overflow-hidden" :class="previewAlignClass()">
                        <img :src="previewUrl"
                            :style="'width:' + (widthUnit === '%' ? width + '%' : width + 'px') +
                            ';max-width:100%;border-radius:6px;'"
                            class="object-contain shadow-sm" alt="Preview">
                    </div>
                </div>

                {{-- Controls --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {{-- Width --}}
                    <div class="space-y-2">
                        <label
                            class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                            Lebar Gambar
                        </label>
                        <div class="flex gap-1">
                            <template
                                x-for="p in [{l:'25%',v:'25',u:'%'},{l:'50%',v:'50',u:'%'},{l:'75%',v:'75',u:'%'},{l:'100%',v:'100',u:'%'}]"
                                :key="p.l">
                                <button type="button" @click="width = p.v; widthUnit = p.u"
                                    :class="width == p.v && widthUnit == p.u ?
                                        'bg-primary-600 text-white border-primary-600' :
                                        'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-primary-400'"
                                    class="flex-1 text-xs rounded-lg border py-1.5 font-medium transition"
                                    x-text="p.l"></button>
                            </template>
                        </div>
                        <div class="flex gap-1.5">
                            <input type="number" x-model="width" min="1"
                                :max="widthUnit === '%' ? 100 : 3000" placeholder="Nilai"
                                class="flex-1 block rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-1.5 px-2.5 focus:ring-primary-500 focus:border-primary-500">
                            <select x-model="widthUnit"
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-1.5 px-2.5 focus:ring-primary-500 focus:border-primary-500">
                                <option value="%">%</option>
                                <option value="px">px</option>
                            </select>
                        </div>
                    </div>

                    {{-- Alignment --}}
                    <div class="space-y-2">
                        <label
                            class="block text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                            Posisi Gambar
                        </label>
                        <div class="grid grid-cols-4 gap-1">
                            <template
                                x-for="opt in [
                                { v: 'none',   icon: 'align-none',   label: 'Default' },
                                { v: 'left',   icon: 'align-left',   label: 'Kiri'    },
                                { v: 'center', icon: 'align-center', label: 'Tengah'  },
                                { v: 'right',  icon: 'align-right',  label: 'Kanan'   }
                            ]"
                                :key="opt.v">
                                <button type="button" @click="align = opt.v"
                                    :class="align === opt.v ?
                                        'bg-primary-600 text-white border-primary-600' :
                                        'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-primary-400'"
                                    :title="opt.label"
                                    class="flex flex-col items-center gap-0.5 rounded-lg border py-2 text-xs font-medium transition">
                                    <template x-if="opt.icon === 'align-none'">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                                            fill="currentColor" class="w-4 h-4">
                                            <path
                                                d="M2 3.75A.75.75 0 0 1 2.75 3h10.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 3.75ZM2 7.75A.75.75 0 0 1 2.75 7h10.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 7.75ZM2 11.75A.75.75 0 0 1 2.75 11h10.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 11.75Z" />
                                        </svg>
                                    </template>
                                    <template x-if="opt.icon === 'align-left'">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                                            fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M2 3.75A.75.75 0 0 1 2.75 3h10.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 3.75ZM2 7.75A.75.75 0 0 1 2.75 7h6a.75.75 0 0 1 0 1.5h-6A.75.75 0 0 1 2 7.75ZM2 11.75A.75.75 0 0 1 2.75 11h8a.75.75 0 0 1 0 1.5h-8A.75.75 0 0 1 2 11.75Z" />
                                        </svg>
                                    </template>
                                    <template x-if="opt.icon === 'align-center'">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                                            fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M2 3.75A.75.75 0 0 1 2.75 3h10.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 3.75ZM5 7.75A.75.75 0 0 1 5.75 7h4.5a.75.75 0 0 1 0 1.5h-4.5A.75.75 0 0 1 5 7.75ZM3.75 11A.75.75 0 0 0 3 11.75a.75.75 0 0 0 .75.75h8.5a.75.75 0 0 0 .75-.75.75.75 0 0 0-.75-.75h-8.5Z" />
                                        </svg>
                                    </template>
                                    <template x-if="opt.icon === 'align-right'">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                                            fill="currentColor" class="w-4 h-4">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M2 3.75A.75.75 0 0 1 2.75 3h10.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 3.75ZM7.25 7a.75.75 0 0 0-.75.75.75.75 0 0 0 .75.75h6a.75.75 0 0 0 .75-.75A.75.75 0 0 0 13.25 7h-6ZM4.75 11a.75.75 0 0 0-.75.75.75.75 0 0 0 .75.75h8.5a.75.75 0 0 0 .75-.75.75.75 0 0 0-.75-.75h-8.5Z" />
                                        </svg>
                                    </template>
                                    <span x-text="opt.label" class="text-[10px] leading-none mt-0.5"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Action row --}}
                <div class="flex items-center justify-between pt-1 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="reset()"
                        class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Ganti Gambar
                    </button>

                    <button type="button" @click="insertImage()" :disabled="!uploadedUrl || uploading"
                        class="inline-flex items-center gap-2 rounded-lg px-5 py-2 text-sm font-semibold text-white
                               bg-primary-600 hover:bg-primary-500 active:bg-primary-700
                               disabled:opacity-50 disabled:cursor-not-allowed
                               focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
                               transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15M9 12l3 3m0 0 3-3m-3 3V2.25" />
                        </svg>
                        <span x-text="uploading ? 'Mengunggah…' : 'Sisipkan ke Editor'"></span>
                    </button>
                </div>

            </div>{{-- /preview+controls --}}
        </div>{{-- /p-4 --}}
    </div>{{-- /panel --}}
</div>
