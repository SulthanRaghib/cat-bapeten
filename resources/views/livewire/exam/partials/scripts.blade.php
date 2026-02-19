@push('scripts')
    <script>
        (function() {
            if (window.__examPageInitialised) {
                return;
            }

            window.__examPageInitialised = true;

            // ============================================================
            // TIMER STATE — single source of truth
            // ============================================================
            var timerInterval = null; // setInterval ID
            var timerEndTime = null; // end timestamp in ms (parsed from ISO)

            var mathRenderDebounce = null;
            var fiveMinuteWarningShown = false;

            // ============================================================
            // HELPERS
            // ============================================================
            function lockExamUI() {
                document.querySelectorAll('input, button, a.nav-btn').forEach(function(el) {
                    el.disabled = true;
                    el.style.pointerEvents = 'none';
                    el.style.opacity = '0.6';
                });
            }

            function prefix(v) {
                return v.toString().padStart(2, '0');
            }

            function formatRemaining(ms) {
                if (ms <= 0) return '00:00';
                var h = Math.floor(ms / 3600000);
                var m = Math.floor((ms % 3600000) / 60000);
                var s = Math.floor((ms % 60000) / 1000);
                return h > 0 ?
                    prefix(h) + ':' + prefix(m) + ':' + prefix(s) :
                    prefix(m) + ':' + prefix(s);
            }

            function getTimerEl() {
                return document.getElementById('exam-timer');
            }

            function setTimerState(timerEl, state) {
                timerEl.setAttribute('data-state', state);
                var container = timerEl.closest('[data-timer-container]');
                if (container) {
                    container.setAttribute('data-state', state);
                    container.classList.remove('timer-warning');
                    if (state === 'warning' || state === 'danger') {
                        container.classList.add('timer-warning');
                    }
                }
            }

            // ============================================================
            // CORE TIMER TICK — called every second by setInterval
            // ============================================================
            function tick() {
                var timerEl = getTimerEl();
                if (!timerEl || !timerEndTime) return;

                var remaining = timerEndTime - Date.now();

                if (remaining <= 0) {
                    timerEl.textContent = '00:00';
                    setTimerState(timerEl, 'danger');
                    lockExamUI();
                    stopInterval();
                    @this.call('handleTimeExpiry');
                    return;
                }

                timerEl.textContent = formatRemaining(remaining);

                // 5-minute warning
                if (remaining <= 5 * 60 * 1000 && !fiveMinuteWarningShown) {
                    fiveMinuteWarningShown = true;
                    showFiveMinuteWarning();
                }

                setTimerState(timerEl, remaining <= 5 * 60 * 1000 ? 'danger' : 'normal');
            }

            // ============================================================
            // INTERVAL MANAGEMENT
            // ============================================================
            function stopInterval() {
                if (timerInterval) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                }
            }

            function startInterval() {
                stopInterval();
                tick(); // Show current value immediately
                timerInterval = setInterval(tick, 1000);
            }

            // ============================================================
            // INIT TIMER — reads data-end-time from DOM
            // ============================================================
            function initTimer() {
                var timerEl = getTimerEl();
                if (!timerEl) {
                    console.warn('Timer element not found, will retry...');
                    return false;
                }

                var endAttr = timerEl.getAttribute('data-end-time');
                if (!endAttr) {
                    timerEl.textContent = '--:--';
                    console.warn('Timer end-time attribute not set');
                    return false;
                }

                var parsed = Date.parse(endAttr);
                if (isNaN(parsed)) {
                    timerEl.textContent = '--:--';
                    console.warn('Timer end-time invalid:', endAttr);
                    return false;
                }

                timerEndTime = parsed;

                // Not paused: start normal countdown
                startInterval();
                console.log('Timer initialized successfully, end time:', new Date(parsed));
                return true;
            }

            // Retry timer initialization up to 10 times with exponential backoff
            function initTimerWithRetry(attempt = 0) {
                if (attempt > 10) {
                    console.error('Failed to initialize timer after 10 attempts');
                    return;
                }

                if (initTimer()) {
                    return; // Success
                }

                var delay = Math.min(100 * Math.pow(1.5, attempt), 3000);
                console.log('Retrying timer init in', delay, 'ms (attempt', attempt + 1, ')');
                setTimeout(function() {
                    initTimerWithRetry(attempt + 1);
                }, delay);
            }

            // ============================================================
            // 5-MINUTE WARNING POPUP
            // ============================================================
            function showFiveMinuteWarning() {
                var warningDiv = document.createElement('div');
                warningDiv.id = 'timer-warning-notif';
                warningDiv.innerHTML =
                    `
                    <div style="
                        position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
                        background: #c62828; color: white; padding: 16px 24px;
                        border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3);
                        z-index: 9999; display: flex; align-items: center; gap: 16px;
                        min-width: 320px; animation: slideDown 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                    ">
                        <span style="background: rgba(255,255,255,0.2); padding: 8px; border-radius: 50%;">
                            <svg style="width: 24px; height: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <div style="flex: 1;">
                            <strong style="display: block; font-size: 16px; margin-bottom: 4px;">Sisa Waktu 5 Menit!</strong>
                            <span style="font-size: 14px; opacity: 0.9;">Segera selesaikan ujian Anda.</span>
                        </div>
                        <button onclick="document.getElementById('timer-warning-notif').remove()" style="
                            background: none; border: none; color: white; padding: 4px;
                            cursor: pointer; opacity: 0.8; transition: opacity 0.2s;">
                            <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <style>@keyframes slideDown { from { top: -100px; opacity: 0; } to { top: 20px; opacity: 1; } }</style>`;
                document.body.appendChild(warningDiv);
                setTimeout(function() {
                    if (document.body.contains(warningDiv)) warningDiv.remove();
                }, 10000);
            }

            // ============================================================
            // MATHJAX
            // ============================================================
            function renderMath() {
                if (window.renderMathJax) {
                    window.renderMathJax();
                    return;
                }
                if (window.MathJax && window.MathJax.typesetPromise) {
                    var nodeList = document.querySelectorAll('.question-content, .option-text');
                    if (nodeList.length) {
                        var nodes = Array.from(nodeList);
                        if (typeof window.MathJax.typesetClear === 'function') {
                            window.MathJax.typesetClear(nodes);
                        }
                        window.MathJax.typesetPromise(nodes).catch(function(err) {
                            console.warn('MathJax error:', err);
                        });
                    }
                }
            }

            function debouncedRenderMath() {
                if (mathRenderDebounce) clearTimeout(mathRenderDebounce);
                mathRenderDebounce = setTimeout(renderMath, 50);
            }

            // ============================================================
            // SAVE INDICATOR
            // ============================================================
            function showSaveIndicator() {
                var indicator = document.getElementById('save-indicator');
                if (!indicator) return;
                indicator.classList.add('show');
                setTimeout(function() {
                    indicator.classList.remove('show');
                }, 1600);
            }

            // ============================================================
            // INIT ENHANCEMENTS (page load / navigation)
            // ============================================================
            function initialiseEnhancements() {
                initTimerWithRetry();
                renderMath();
            }

            // Initial setup
            if (document.readyState !== 'loading') {
                initialiseEnhancements();
            } else {
                document.addEventListener('DOMContentLoaded', initialiseEnhancements);
            }

            // Watch for timer element changes (when Livewire updates it)
            var timerObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'data-end-time') {
                        var target = mutation.target;
                        if (target.id === 'exam-timer') {
                            console.log('Timer data-end-time changed, reinitializing...');
                            initTimerWithRetry();
                        }
                    }
                });
            });

            // Handle exam-started event directly
            document.addEventListener('livewire:init', function() {
                Livewire.on('exam-started', (endTime) => {
                    console.log('Event exam-started received', endTime);

                    // The endTime might be an array if sent as parameter, or string
                    var timeStr = Array.isArray(endTime) ? endTime[0] : endTime;

                    var timerEl = getTimerEl();
                    if (timerEl) {
                        timerEl.setAttribute('data-end-time', timeStr);
                        // Trigger re-init manually immediately
                        initTimerWithRetry();
                    } else {
                        // If DOM not ready yet, wait a bit
                        setTimeout(() => {
                            var retryEl = getTimerEl();
                            if (retryEl) {
                                retryEl.setAttribute('data-end-time', timeStr);
                                initTimerWithRetry();
                            }
                        }, 500);
                    }
                });
            });

            // Start observing timer element once it exists
            setTimeout(function watchTimer() {
                var timerEl = getTimerEl();
                if (timerEl) {
                    timerObserver.observe(timerEl, {
                        attributes: true,
                        attributeFilter: ['data-end-time']
                    });
                    console.log('Timer observer attached');
                } else {
                    setTimeout(watchTimer, 500);
                }
            }, 100);

            // ============================================================
            // LIVEWIRE EVENT HOOKS
            // ============================================================
            document.addEventListener('livewire:init', function() {

                Livewire.hook('morph.updated', function({
                    el,
                    component
                }) {
                    debouncedRenderMath();
                });

                Livewire.on('question-changed', function() {
                    // Scroll to absolute top of the page
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });

                Livewire.on('answer-saved', function() {
                    showSaveIndicator();
                });

                // -------- STOPPED: force-finished by admin --------
                Livewire.on('exam-stopped', function(data) {
                    stopInterval();
                    lockExamUI();

                    var timerEl = getTimerEl();
                    if (timerEl) {
                        timerEl.textContent = '00:00';
                        setTimerState(timerEl, 'danger');
                    }
                });

                // -------- QUESTION CHANGED: re-render MathJax only --------
                Livewire.on('question-changed', function() {
                    requestAnimationFrame(function() {
                        renderMath();
                        initTimerWithRetry();
                    });
                });

                // -------- EXAM FINISHED: cleanup --------
                Livewire.on('exam-finished', function() {
                    stopInterval();

                    if (window.activeExamStream) {
                        window.activeExamStream.getTracks().forEach(track => track.stop());
                        window.activeExamStream = null;
                    }

                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        navigator.mediaDevices.getUserMedia({
                                video: true,
                                audio: false
                            })
                            .then(function(stream) {
                                stream.getTracks().forEach(function(track) {
                                    track.stop();
                                });
                            }).catch(function() {});
                    }

                    document.querySelectorAll('video').forEach(function(vid) {
                        if (vid.srcObject) {
                            vid.srcObject.getTracks().forEach(track => track.stop());
                        }
                        vid.pause();
                        vid.src = "";
                    });
                });
            });

            // Fallback for initial page load (SPA navigation)
            document.addEventListener('livewire:navigated', function() {
                initTimerWithRetry();
                renderMath();
            });
        })();

        // ============================================================
        // EXAM CLIENT — Alpine.js component factory
        // Defined OUTSIDE the IIFE so it is always available at page
        // load time, even before the exam step is rendered via Livewire.
        // Dynamically-injected <script> tags (via DOM morphing) are NOT
        // executed by browsers, so this MUST live in the initial HTML.
        // ============================================================
        window.createExamClient = function(initialQuestions, initialAnswers, wireEntangle) {
            return {
                questions: initialQuestions || [],
                answersMap: initialAnswers || {},
                currentIndex: wireEntangle,

                init() {
                    this.startCamera();
                },

                get totalQuestions() { return this.questions.length; },

                get currentQuestion() {
                    return (this.questions && this.questions[this.currentIndex]) || {
                        id: null,
                        question_text: '',
                        options: []
                    };
                },

                get normalizedOptions() {
                    let options = this.currentQuestion.options;
                    if (!options) return [];
                    if (typeof options === 'string') {
                        try { options = JSON.parse(options); } catch(e) { options = []; }
                    }
                    let result = [];
                    if (Array.isArray(options)) {
                        options.forEach((opt, idx) => {
                            let text = '';
                            if (typeof opt === 'string') text = opt;
                            else if (opt && opt.answer_text) text = opt.answer_text;
                            else if (opt && opt.teks) text = opt.teks;
                            result.push({ value: String(idx), text: text });
                        });
                    }
                    return result;
                },

                get currentAnswerData() {
                    return this.answersMap[this.currentQuestion.id] || { answer: null, doubtful: false };
                },

                get isDoubtful() {
                    return !!this.currentAnswerData.doubtful;
                },

                get stats() {
                    let answered = 0, doubtful = 0;
                    let total = this.totalQuestions;
                    Object.values(this.answersMap).forEach(a => {
                        if (a.doubtful) doubtful++;
                        else if (a.answer !== null && a.answer !== '') answered++;
                    });
                    return { answered, doubtful, unanswered: total - answered - doubtful };
                },

                isAnswerSelected(val) {
                    let saved = this.currentAnswerData.answer;
                    return saved !== null && saved === String(val);
                },

                selectExample(val) {
                    let valStr = String(val);
                    let qId = this.currentQuestion.id;
                    if (!this.answersMap[qId]) this.answersMap[qId] = { answer: null, doubtful: false };
                    this.answersMap[qId].answer = valStr;
                    this.$wire.saveAnswerClient(qId, valStr);
                },

                toggleDoubtful() {
                    let qId = this.currentQuestion.id;
                    if (!this.answersMap[qId]) this.answersMap[qId] = { answer: null, doubtful: false };
                    let newState = !this.answersMap[qId].doubtful;
                    this.answersMap[qId].doubtful = newState;
                    this.$wire.toggleDoubtfulClient(qId, newState);
                },

                next() {
                    if (this.currentIndex < this.totalQuestions - 1) {
                        this.currentIndex++;
                        this.scrollToTop();
                    }
                },

                prev() {
                    if (this.currentIndex > 0) {
                        this.currentIndex--;
                        this.scrollToTop();
                    }
                },

                jumpTo(idx) {
                    this.currentIndex = idx;
                    this.scrollToTop();
                },

                scrollToTop() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                getSidebarClass(qId, idx) {
                    let data = this.answersMap[qId];
                    let classes = [];
                    if (data && data.doubtful) classes.push('doubt');
                    else if (data && data.answer !== null && data.answer !== '') classes.push('answered');
                    if (idx === this.currentIndex) classes.push('current');
                    return classes.join(' ');
                },

                async startCamera() {
                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        try {
                            if (window.activeExamStream) {
                                window.activeExamStream.getTracks().forEach(t => t.stop());
                            }
                            const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                            if (this.$refs.proctorVideo) {
                                this.$refs.proctorVideo.srcObject = stream;
                            }
                            window.activeExamStream = stream;
                        } catch (err) {
                            console.error('Proctoring Camera Failed:', err);
                        }
                    }
                }
            };
        };
    </script>
@endpush
