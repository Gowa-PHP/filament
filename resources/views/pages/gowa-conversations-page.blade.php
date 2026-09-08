<x-filament-panels::page>
    <style>
        .gowa-inbox-wrapper {
            --gowa-bg: #ffffff;
            --gowa-border: #e5e7eb;
            --gowa-text: #111827;
            --gowa-text-muted: #6b7280;
            --gowa-sidebar-bg: #f9fafb;
            --gowa-active-item: rgba(14, 165, 233, 0.08);
            --gowa-active-border: #0284c7;
            --gowa-chat-bg: #efeae2;
            --gowa-bubble-in: #ffffff;
            --gowa-bubble-in-border: #e5e7eb;
            --gowa-bubble-in-text: #111827;
            --gowa-bubble-out: #d9fdd3;
            --gowa-bubble-out-border: #bbf7d0;
            --gowa-bubble-out-text: #111827;
            --gowa-composer-bg: #f9fafb;
        }

        .dark .gowa-inbox-wrapper,
        [data-theme="dark"] .gowa-inbox-wrapper {
            --gowa-bg: #111827;
            --gowa-border: rgba(255, 255, 255, 0.1);
            --gowa-text: #f9fafb;
            --gowa-text-muted: #9ca3af;
            --gowa-sidebar-bg: #111827;
            --gowa-active-item: rgba(14, 165, 233, 0.15);
            --gowa-active-border: #38bdf8;
            --gowa-chat-bg: #0b141a;
            --gowa-bubble-in: #202c33;
            --gowa-bubble-in-border: #2a3942;
            --gowa-bubble-in-text: #e9edef;
            --gowa-bubble-out: #005c4b;
            --gowa-bubble-out-border: #026955;
            --gowa-bubble-out-text: #e9edef;
            --gowa-composer-bg: #111827;
        }

        .gowa-inbox-container {
            display: flex;
            flex-direction: row;
            width: 100%;
            height: calc(100vh - 12.5rem);
            min-height: 580px;
            background-color: var(--gowa-bg);
            border: 1px solid var(--gowa-border);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        .gowa-sidebar {
            width: 360px;
            min-width: 300px;
            max-width: 400px;
            height: 100%;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--gowa-border);
            background-color: var(--gowa-bg);
            flex-shrink: 0;
        }

        .gowa-sidebar-header {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid var(--gowa-border);
            background-color: var(--gowa-sidebar-bg);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .gowa-sidebar-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .gowa-sidebar-title h3 {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--gowa-text);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .gowa-sidebar-search {
            position: relative;
            width: 100%;
        }

        .gowa-sidebar-tabs {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem;
            background-color: rgba(0, 0, 0, 0.05);
            border-radius: 0.5rem;
        }

        .dark .gowa-sidebar-tabs {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .gowa-tab-btn {
            flex: 1;
            padding: 0.375rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            border: none;
            background: transparent;
            color: var(--gowa-text-muted);
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
        }

        .gowa-tab-btn.is-active {
            background-color: var(--gowa-bg);
            color: var(--gowa-text);
            font-weight: 600;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .gowa-conv-list {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .gowa-conv-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            cursor: pointer;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            transition: background-color 0.1s ease;
            user-select: none;
        }

        .dark .gowa-conv-item {
            border-bottom-color: rgba(255, 255, 255, 0.04);
        }

        .gowa-conv-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .dark .gowa-conv-item:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }

        .gowa-conv-item.is-active {
            background-color: var(--gowa-active-item);
            border-left: 3px solid var(--gowa-active-border);
        }

        .gowa-avatar {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8125rem;
            flex-shrink: 0;
        }

        .gowa-conv-details {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .gowa-conv-top-line {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .gowa-conv-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gowa-text);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .gowa-conv-time {
            font-size: 0.6875rem;
            color: var(--gowa-text-muted);
            flex-shrink: 0;
        }

        .gowa-conv-bottom-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .gowa-conv-preview {
            font-size: 0.75rem;
            color: var(--gowa-text-muted);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin: 0;
        }

        .gowa-badge-unread {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 1.25rem;
            min-width: 1.25rem;
            padding: 0 0.375rem;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            background-color: #10b981;
            color: #ffffff;
            flex-shrink: 0;
        }

        .gowa-chat {
            flex: 1;
            min-width: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            background-color: var(--gowa-chat-bg);
        }

        .gowa-chat-header {
            padding: 0.625rem 1rem;
            border-bottom: 1px solid var(--gowa-border);
            background-color: var(--gowa-bg);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        .gowa-chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 0.625rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .gowa-date-separator {
            display: flex;
            justify-content: center;
            margin: 0.35rem 0;
        }

        .gowa-date-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.65rem;
            font-weight: 500;
            background-color: var(--gowa-bg);
            color: var(--gowa-text-muted);
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.04);
            border: 1px solid var(--gowa-border);
        }

        .gowa-msg-row {
            display: flex;
            width: 100%;
        }

        .gowa-msg-row.is-inbound {
            justify-content: flex-start;
        }

        .gowa-msg-row.is-outbound {
            justify-content: flex-end;
        }

        .gowa-bubble {
            position: relative;
            max-width: 65%;
            min-width: 3.5rem;
            padding: 0.3rem 0.55rem 0.25rem 0.55rem;
            border-radius: 0.45rem;
            font-size: 0.8125rem;
            line-height: 1.35;
            box-shadow: 0 1px 0.5px rgba(0, 0, 0, 0.13);
            word-break: break-word;
            display: flow-root;
        }

        .gowa-bubble::after {
            content: "";
            display: table;
            clear: both;
        }

        .gowa-bubble.is-inbound {
            border-top-left-radius: 0.125rem;
            background-color: var(--gowa-bubble-in);
            border: 1px solid var(--gowa-bubble-in-border);
            color: var(--gowa-bubble-in-text);
        }

        .gowa-bubble.is-outbound {
            border-top-right-radius: 0.125rem;
            background-color: var(--gowa-bubble-out);
            border: 1px solid var(--gowa-bubble-out-border);
            color: var(--gowa-bubble-out-text);
        }

        .gowa-bubble-text {
            white-space: pre-wrap;
            word-break: break-word;
            font-size: 0.8125rem;
            line-height: 1.35;
        }

        .gowa-bubble-meta {
            float: right;
            display: inline-flex;
            align-items: center;
            gap: 0.15rem;
            margin-left: 0.45rem;
            margin-top: 0.2rem;
            font-size: 0.625rem;
            line-height: 1;
            opacity: 0.65;
            user-select: none;
            vertical-align: bottom;
        }

        .gowa-chat-composer {
            padding: 0.5rem 0.75rem;
            border-top: 1px solid var(--gowa-border);
            background-color: var(--gowa-composer-bg);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .gowa-empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            text-align: center;
            gap: 0.75rem;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .gowa-inbox-container {
                flex-direction: column;
                height: calc(100vh - 10rem);
            }
            .gowa-sidebar {
                width: 100% !important;
                max-width: 100% !important;
                border-right: none !important;
            }
            .gowa-sidebar.is-hidden {
                display: none !important;
            }
            .gowa-chat.is-hidden {
                display: none !important;
            }
            .gowa-bubble {
                max-width: 82%;
            }
        }
    </style>

    <div class="gowa-inbox-wrapper">
        <div class="gowa-inbox-container">
            <!-- Left Sidebar: Conversation List -->
            <div class="gowa-sidebar {{ $selectedConversationId ? 'is-hidden' : '' }}">
                @include('gowa-filament::pages.partials._conversation-list')
            </div>

            <!-- Right Panel: Chat Area -->
            <div class="gowa-chat {{ ! $selectedConversationId ? 'is-hidden' : '' }}">
                @include('gowa-filament::pages.partials._chat-panel')
            </div>
        </div>
    </div>
</x-filament-panels::page>
