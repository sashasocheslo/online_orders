@props([
    'menu',
    'providers' => [],
    'availableProviders' => [],
    'conversation' => [],
])

@auth
    @php
        $assistantId = 'ai-assistant-'.$menu->id;
        $defaultProvider = config('services.ai.default');
        $selectedProvider = in_array($defaultProvider, $availableProviders, true)
            ? $defaultProvider
            : ($availableProviders[0] ?? null);
        $hasAvailableProvider = $selectedProvider !== null;
    @endphp

    <style>
        #{{ $assistantId }}.ai-chat-widget {
            position: fixed;
            right: 1rem;
            bottom: 1rem;
            z-index: 1080;
            color: #212529;
            font-family: inherit;
        }

        #{{ $assistantId }} .ai-chat-toggle {
            width: 3.4rem;
            height: 3.4rem;
            border-radius: 50%;
            box-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .25);
        }

        #{{ $assistantId }} .ai-chat-panel {
            position: fixed;
            right: 1rem;
            bottom: 5rem;
            display: flex;
            flex-direction: column;
            width: min(21.5rem, calc(100vw - 1.5rem));
            max-height: calc(100vh - 6rem);
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: .9rem;
            background: #fff;
            color: #212529;
            text-align: left !important;
            box-shadow: 0 .75rem 2rem rgba(0, 0, 0, .28);
        }

        #{{ $assistantId }} .ai-chat-panel,
        #{{ $assistantId }} .ai-chat-panel * {
            box-sizing: border-box;
        }

        #{{ $assistantId }} .ai-chat-header {
            padding: .7rem .85rem;
        }

        #{{ $assistantId }} .ai-chat-messages {
            display: flex;
            flex-direction: column;
            align-items: flex-start !important;
            gap: .55rem;
            min-height: 5.5rem;
            max-height: 21rem;
            padding: .75rem;
            overflow-y: auto;
            background: #f8f9fa;
            text-align: left !important;
        }

        #{{ $assistantId }} .ai-chat-message {
            display: block !important;
            position: relative;
            flex: 0 0 auto !important;
            align-self: flex-start;
            width: fit-content;
            max-width: 86%;
            height: auto !important;
            min-height: 0 !important;
            padding: .6rem .75rem;
            border-radius: .8rem;
            font-size: .93rem;
            line-height: 1.4;
            text-align: left !important;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        #{{ $assistantId }} .ai-chat-message-text {
            display: block !important;
            width: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            text-align: left !important;
        }

        #{{ $assistantId }} .ai-chat-message-user {
            align-self: flex-end;
            color: #fff;
            background: var(--bs-primary);
        }

        #{{ $assistantId }} .ai-chat-message-assistant {
            color: #212529;
            background: #eaf2ff;
            border: 0;
            border-bottom-left-radius: .2rem;
        }

        #{{ $assistantId }} .ai-chat-message-assistant::after {
            position: absolute;
            left: -.35rem;
            bottom: 0;
            width: .7rem;
            height: .7rem;
            background: #eaf2ff;
            clip-path: polygon(100% 0, 100% 100%, 0 100%);
            content: '';
        }

        #{{ $assistantId }} .ai-chat-activity {
            align-self: flex-start;
            padding: 0 .2rem;
            color: #6c757d;
            font-size: .78rem;
            line-height: 1.3;
        }

        #{{ $assistantId }} .ai-chat-activity-success {
            color: #198754;
        }

        #{{ $assistantId }} .ai-chat-activity-warning {
            color: #9a6700;
        }

        #{{ $assistantId }} .ai-chat-activity-error {
            color: #dc3545;
        }

        #{{ $assistantId }} .ai-chat-products {
            display: grid;
            align-self: stretch;
            gap: .55rem;
            width: 100%;
        }

        #{{ $assistantId }} .ai-chat-product {
            display: grid;
            grid-template-columns: 4rem minmax(0, 1fr);
            gap: .65rem;
            padding: .55rem;
            border: 1px solid #dee2e6;
            border-radius: .75rem;
            background: #fff;
        }

        #{{ $assistantId }} .ai-chat-product-image {
            width: 4rem;
            height: 4rem;
            border-radius: .55rem;
            object-fit: cover;
        }

        #{{ $assistantId }} .ai-chat-product-name {
            display: block;
            color: #212529;
            font-size: .86rem;
            font-weight: 700;
            line-height: 1.2;
            text-decoration: none;
        }

        #{{ $assistantId }} .ai-chat-product-price {
            margin-top: .2rem;
            color: #212529;
            font-size: .84rem;
            font-weight: 600;
        }

        #{{ $assistantId }} .ai-chat-product-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            margin-top: .4rem;
        }

        #{{ $assistantId }} .ai-chat-product-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 1.8rem;
            padding: .25rem .5rem;
            border: 1px solid var(--bs-primary);
            border-radius: .45rem;
            color: var(--bs-primary);
            background: #fff;
            font-size: .75rem;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
        }

        #{{ $assistantId }} .ai-chat-product-action-primary {
            color: #fff;
            background: var(--bs-primary);
        }

        #{{ $assistantId }} .ai-chat-form {
            padding: .7rem .75rem .75rem;
            border-top: 1px solid #e9ecef;
            background: #fff;
            text-align: left !important;
        }

        #{{ $assistantId }} .ai-chat-provider {
            display: block;
            width: 100%;
            height: 2.25rem;
            padding: .3rem 2rem .3rem .65rem;
            border: 1px solid #ced4da;
            border-radius: .55rem;
            background-color: #fff;
            color: #212529;
            font-size: .9rem;
        }

        #{{ $assistantId }} .ai-chat-composer {
            display: flex;
            align-items: center;
            gap: .35rem;
            margin-top: .55rem;
            padding: .3rem;
            border: 1px solid #ced4da;
            border-radius: 1rem;
            background: #fff;
        }

        #{{ $assistantId }} .ai-chat-question {
            display: block;
            flex: 1 1 auto;
            width: 100%;
            min-width: 0;
            height: 3rem;
            padding: .45rem .5rem;
            overflow: hidden;
            border: 0;
            border-radius: .7rem;
            outline: none;
            resize: none;
            color: #212529;
            background: #fff;
            font-size: .9rem;
            line-height: 1.35;
        }

        #{{ $assistantId }} .ai-chat-provider:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .18);
        }

        #{{ $assistantId }} .ai-chat-composer:focus-within {
            border-color: #86b7fe;
            box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .18);
        }

        #{{ $assistantId }} .ai-chat-send {
            display: inline-flex;
            flex: 0 0 2.75rem;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            padding: 0;
            border: 0;
            border-radius: 50%;
            color: #fff;
            background: var(--bs-primary);
            cursor: pointer;
        }

        #{{ $assistantId }} .ai-chat-send:hover {
            background: #0b5ed7;
        }

        #{{ $assistantId }} .ai-chat-send:disabled {
            cursor: wait;
            opacity: .65;
        }

        @media (max-width: 575.98px) {
            #{{ $assistantId }}.ai-chat-widget {
                right: .75rem;
                bottom: .75rem;
            }

            #{{ $assistantId }} .ai-chat-panel {
                right: .75rem;
                bottom: 4.75rem;
                width: calc(100vw - 1.5rem);
            }
        }
    </style>

    <aside
        id="{{ $assistantId }}"
        class="ai-chat-widget text-start"
        data-cart-url="{{ route('cart_product.store') }}"
        data-cart-page-url="{{ route('menu.cart.index', $menu) }}"
        data-reset-url="{{ route('menu.ai.conversation.destroy', $menu) }}"
    >
        <section
            id="{{ $assistantId }}-panel"
            data-ai-assistant-panel
            class="ai-chat-panel d-none"
            aria-label="AI-помічник ресторану {{ $menu->name }}"
        >
            <header class="ai-chat-header bg-primary text-white d-flex align-items-center justify-content-between gap-2">
                <div>
                    <div class="fw-bold">AI-помічник</div>
                    <small>{{ $menu->name }}</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-light py-1 px-2"
                        data-ai-assistant-reset
                    >
                        Очистити
                    </button>
                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-ai-assistant-close
                        aria-label="Закрити чат"
                    ></button>
                </div>
            </header>

            <div data-ai-assistant-messages class="ai-chat-messages">
                @if ($conversation === [])
                    <div
                        class="ai-chat-message ai-chat-message-assistant"
                        style="display: block !important; flex: 0 0 auto !important; width: fit-content !important; height: auto !important; min-height: 0 !important; text-align: left !important; align-self: flex-start !important"
                    >
                        <span class="ai-chat-message-text">Вітаю! 👋 Чим допомогти?</span>
                    </div>
                @else
                    @foreach ($conversation as $message)
                        <div
                            class="ai-chat-message ai-chat-message-{{ $message['role'] }}"
                            style="display: block !important; flex: 0 0 auto !important; width: fit-content !important; height: auto !important; min-height: 0 !important; text-align: left !important; align-self: {{ $message['role'] === 'user' ? 'flex-end' : 'flex-start' }} !important"
                        >
                            <span class="ai-chat-message-text">{{ $message['content'] }}</span>
                        </div>
                    @endforeach
                @endif
            </div>

            <form
                data-ai-assistant-form
                action="{{ route('menu.ai.recommendations', $menu) }}"
                method="POST"
                class="ai-chat-form"
            >
                @csrf

                <label for="ai-provider-{{ $menu->id }}" class="form-label small text-secondary mb-1">
                    AI-провайдер
                </label>
                <select
                    id="ai-provider-{{ $menu->id }}"
                    name="provider"
                    class="ai-chat-provider"
                    required
                    @disabled(! $hasAvailableProvider)
                >
                    @foreach ($providers as $provider)
                        @php($configured = in_array($provider->value, $availableProviders, true))
                        <option
                            value="{{ $provider->value }}"
                            @selected($provider->value === $selectedProvider)
                            @disabled(! $configured)
                        >
                            {{ $provider->label() }}{{ $configured ? '' : ' — не налаштовано' }}
                        </option>
                    @endforeach
                </select>

                <div class="ai-chat-composer">
                    <textarea
                        id="ai-question-{{ $menu->id }}"
                        name="question"
                        class="ai-chat-question"
                        rows="2"
                        minlength="3"
                        maxlength="500"
                        placeholder="Наприклад: ситна страва до 200 грн без гострого"
                        aria-label="Ваше повідомлення AI-помічнику"
                        required
                        @disabled(! $hasAvailableProvider)
                    ></textarea>
                    <button
                        type="submit"
                        class="ai-chat-send"
                        data-ai-assistant-submit
                        aria-label="Надіслати повідомлення"
                        @disabled(! $hasAvailableProvider)
                    >
                        <i class="bi bi-send-fill" aria-hidden="true"></i>
                    </button>
                </div>

                @unless ($hasAvailableProvider)
                    <p class="small text-secondary mt-2 mb-0">
                        AI-провайдери ще не налаштовані. Додайте серверний API-ключ у локальний <code>.env</code>.
                    </p>
                @endunless

                <div
                    data-ai-assistant-status
                    class="visually-hidden"
                    role="status"
                    aria-live="polite"
                ></div>
            </form>
        </section>

        <button
            type="button"
            class="ai-chat-toggle btn btn-primary d-flex align-items-center justify-content-center"
            data-ai-assistant-toggle
            aria-controls="{{ $assistantId }}-panel"
            aria-expanded="false"
            aria-label="Відкрити AI-помічника {{ $menu->name }}"
            title="AI-помічник {{ $menu->name }}"
        >
            <i class="bi bi-chat-dots-fill fs-4" aria-hidden="true"></i>
        </button>
    </aside>

    <script>
        (() => {
            const root = document.getElementById(@js($assistantId));

            if (!root) {
                return;
            }

            const panel = root.querySelector('[data-ai-assistant-panel]');
            const toggleButton = root.querySelector('[data-ai-assistant-toggle]');
            const closeButton = root.querySelector('[data-ai-assistant-close]');
            const resetButton = root.querySelector('[data-ai-assistant-reset]');
            const form = root.querySelector('[data-ai-assistant-form]');
            const questionInput = form.querySelector('[name="question"]');
            const submitButton = root.querySelector('[data-ai-assistant-submit]');
            const statusElement = root.querySelector('[data-ai-assistant-status]');
            const messagesElement = root.querySelector('[data-ai-assistant-messages]');
            const cartUrl = root.dataset.cartUrl;
            const cartPageUrl = root.dataset.cartPageUrl;
            const resetUrl = root.dataset.resetUrl;
            const csrfToken = form.querySelector('[name="_token"]').value;

            const setPanelOpen = (isOpen) => {
                panel.classList.toggle('d-none', !isOpen);
                toggleButton.setAttribute('aria-expanded', String(isOpen));

                if (isOpen && !questionInput.disabled) {
                    questionInput.focus();
                }
            };

            const appendMessage = (text, sender) => {
                const message = document.createElement('div');
                message.className = `ai-chat-message ai-chat-message-${sender}`;
                message.style.setProperty('display', 'block', 'important');
                message.style.setProperty('flex', '0 0 auto', 'important');
                message.style.setProperty('width', 'fit-content', 'important');
                message.style.setProperty('height', 'auto', 'important');
                message.style.setProperty('min-height', '0', 'important');
                message.style.setProperty('text-align', 'left', 'important');
                message.style.setProperty(
                    'align-self',
                    sender === 'user' ? 'flex-end' : 'flex-start',
                    'important',
                );
                const messageText = document.createElement('span');
                messageText.className = 'ai-chat-message-text';
                messageText.textContent = text;
                message.appendChild(messageText);
                messagesElement.appendChild(message);
                messagesElement.scrollTop = messagesElement.scrollHeight;

                return message;
            };

            const appendActivity = (text, tone = 'muted') => {
                const activity = document.createElement('div');
                activity.className = `ai-chat-activity ai-chat-activity-${tone}`;
                activity.textContent = text;
                messagesElement.appendChild(activity);
                messagesElement.scrollTop = messagesElement.scrollHeight;

                return activity;
            };

            const appendProductCards = (products, csrfToken) => {
                if (!Array.isArray(products) || products.length === 0) {
                    return;
                }

                const list = document.createElement('div');
                list.className = 'ai-chat-products';

                products.forEach((product) => {
                    const card = document.createElement('article');
                    card.className = 'ai-chat-product';

                    const imageLink = document.createElement('a');
                    imageLink.href = product.url;

                    const image = document.createElement('img');
                    image.className = 'ai-chat-product-image';
                    image.src = product.image_url;
                    image.alt = product.name;
                    image.loading = 'lazy';
                    imageLink.appendChild(image);

                    const content = document.createElement('div');
                    const nameLink = document.createElement('a');
                    nameLink.className = 'ai-chat-product-name';
                    nameLink.href = product.url;
                    nameLink.textContent = product.name;

                    const price = document.createElement('div');
                    price.className = 'ai-chat-product-price';
                    price.textContent = product.price;

                    const actions = document.createElement('div');
                    actions.className = 'ai-chat-product-actions';

                    const viewLink = document.createElement('a');
                    viewLink.className = 'ai-chat-product-action';
                    viewLink.href = product.url;
                    viewLink.textContent = 'Переглянути';

                    const addButton = document.createElement('button');
                    addButton.type = 'button';
                    addButton.className = 'ai-chat-product-action ai-chat-product-action-primary';
                    addButton.textContent = 'У кошик';
                    addButton.addEventListener('click', async () => {
                        addButton.disabled = true;
                        addButton.textContent = 'Додаємо…';

                        try {
                            const response = await fetch(cartUrl, {
                                method: 'POST',
                                headers: {
                                    Accept: 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                                body: JSON.stringify({ product_id: product.id }),
                            });

                            const responseData = await response.json();

                            if (!response.ok) {
                                throw new Error(responseData.message || 'Не вдалося додати товар.');
                            }

                            addButton.textContent = 'Додано ✓';
                            appendActivity(`${product.name} додано в кошик.`, 'success');
                        } catch (error) {
                            addButton.disabled = false;
                            addButton.textContent = 'У кошик';
                            appendActivity(
                                error instanceof Error ? error.message : 'Не вдалося додати товар.',
                                'error',
                            );
                        }
                    });

                    const cartLink = document.createElement('a');
                    cartLink.className = 'ai-chat-product-action';
                    cartLink.href = cartPageUrl;
                    cartLink.textContent = 'До кошика';

                    actions.append(viewLink, addButton, cartLink);
                    content.append(nameLink, price, actions);
                    card.append(imageLink, content);
                    list.appendChild(card);
                });

                messagesElement.appendChild(list);
                messagesElement.scrollTop = messagesElement.scrollHeight;
            };

            toggleButton.addEventListener('click', () => {
                setPanelOpen(panel.classList.contains('d-none'));
            });

            closeButton.addEventListener('click', () => setPanelOpen(false));

            resetButton.addEventListener('click', async () => {
                resetButton.disabled = true;

                try {
                    const response = await fetch(resetUrl, {
                        method: 'DELETE',
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Не вдалося очистити діалог.');
                    }

                    messagesElement.replaceChildren();
                    appendMessage('Вітаю! 👋 Чим допомогти?', 'assistant');
                    statusElement.textContent = 'Діалог очищено.';
                } catch (error) {
                    const message = error instanceof Error
                        ? error.message
                        : 'Не вдалося очистити діалог.';

                    appendActivity(message, 'error');
                    statusElement.textContent = message;
                } finally {
                    resetButton.disabled = false;
                }
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const formData = new FormData(form);
                const question = String(formData.get('question') || '').trim();

                if (question === '') {
                    return;
                }

                appendMessage(question, 'user');
                const pendingActivity = appendActivity('AI аналізує меню…');
                questionInput.value = '';
                submitButton.disabled = true;
                statusElement.textContent = 'AI аналізує меню…';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': formData.get('_token'),
                        },
                        body: JSON.stringify({
                            provider: formData.get('provider'),
                            question,
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        const validationMessage = data.errors
                            ? Object.values(data.errors).flat().join(' ')
                            : null;

                        throw new Error(validationMessage || data.message || 'Не вдалося отримати відповідь.');
                    }

                    pendingActivity.remove();
                    appendMessage(data.answer, 'assistant');
                    appendProductCards(data.products, formData.get('_token'));

                    if (data.fallback) {
                        appendActivity('Показано локальний резервний підбір.', 'warning');
                        statusElement.textContent = 'Локальний резервний підбір.';
                    } else {
                        appendActivity(`Відповів ${data.provider_label}.`, 'success');
                        statusElement.textContent = `Відповів ${data.provider_label}.`;
                    }
                } catch (error) {
                    pendingActivity.remove();
                    const message = error instanceof Error
                        ? error.message
                        : 'Не вдалося отримати відповідь. Спробуйте пізніше.';

                    appendMessage(message, 'assistant');
                    appendActivity('Запит не виконано.', 'error');
                    statusElement.textContent = 'Запит не виконано.';
                } finally {
                    submitButton.disabled = false;
                    questionInput.focus();
                }
            });
        })();
    </script>
@endauth
