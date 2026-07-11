<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocsController extends Controller
{
    public function index()
    {
        $demoAccounts = $this->demoAccounts();
        $menuGroups = $this->menuGroups();
        $tutorialPhases = $this->tutorialPhases();
        $features = $this->features();

        $seoMeta = [
            'title' => __('docs.seo_title'),
            'description' => __('docs.seo_description'),
            'canonical' => url('/docs'),
        ];

        return view('docs.index', compact(
            'demoAccounts', 'menuGroups', 'tutorialPhases', 'features', 'seoMeta'
        ));
    }

    protected function demoAccounts(): array
    {
        return [
            ['role' => 'Admin', 'email' => 'admin@wabot.test', 'password' => 'password', 'scope' => __('docs.demo.admin_scope')],
            ['role' => 'User', 'email' => 'user@wabot.test', 'password' => 'password', 'scope' => __('docs.demo.user_scope')],
        ];
    }

    protected function menuGroups(): array
    {
        return [
            [
                'title' => __('docs.menu.group_main'),
                'icon' => 'fa-star',
                'items' => [
                    ['icon' => 'fa-comments', 'label' => __('docs.menu.chat_omnichannel'), 'desc' => __('docs.menu.chat_omnichannel_desc')],
                    ['icon' => 'fa-chart-pie', 'label' => __('docs.menu.dashboard'), 'desc' => __('docs.menu.dashboard_desc')],
                ]
            ],
            [
                'title' => __('docs.menu.group_whatsapp'),
                'icon' => 'fa-whatsapp',
                'items' => [
                    ['icon' => 'fa-mobile-alt', 'label' => __('docs.menu.sessions'), 'desc' => __('docs.menu.sessions_desc')],
                    ['icon' => 'fa-address-book', 'label' => __('docs.menu.contacts'), 'desc' => __('docs.menu.contacts_desc')],
                    ['icon' => 'fa-layer-group', 'label' => __('docs.menu.contact_groups'), 'desc' => __('docs.menu.contact_groups_desc')],
                    ['icon' => 'fa-bullhorn', 'label' => __('docs.menu.campaigns'), 'desc' => __('docs.menu.campaigns_desc')],
                    ['icon' => 'fa-clock', 'label' => __('docs.menu.recurring'), 'desc' => __('docs.menu.recurring_desc')],
                    ['icon' => 'fa-robot', 'label' => __('docs.menu.autoreply'), 'desc' => __('docs.menu.autoreply_desc')],
                    ['icon' => 'fa-file-lines', 'label' => __('docs.menu.templates'), 'desc' => __('docs.menu.templates_desc')],
                    ['icon' => 'fa-bolt', 'label' => __('docs.menu.webhooks'), 'desc' => __('docs.menu.webhooks_desc')],
                    ['icon' => 'fa-brain', 'label' => __('docs.menu.ai_keys'), 'desc' => __('docs.menu.ai_keys_desc')],
                ]
            ],
            [
                'title' => __('docs.menu.group_system'),
                'icon' => 'fa-cog',
                'items' => [
                    ['icon' => 'fa-server', 'label' => __('docs.menu.servers'), 'desc' => __('docs.menu.servers_desc')],
                    ['icon' => 'fa-users-cog', 'label' => __('docs.menu.users'), 'desc' => __('docs.menu.users_desc')],
                    ['icon' => 'fa-ticket-alt', 'label' => __('docs.menu.vouchers'), 'desc' => __('docs.menu.vouchers_desc')],
                    ['icon' => 'fa-exchange-alt', 'label' => __('docs.menu.transactions'), 'desc' => __('docs.menu.transactions_desc')],
                    ['icon' => 'fa-link', 'label' => __('docs.menu.shortener'), 'desc' => __('docs.menu.shortener_desc')],
                    ['icon' => 'fa-file-alt', 'label' => __('docs.menu.cms_pages'), 'desc' => __('docs.menu.cms_pages_desc')],
                    ['icon' => 'fa-blog', 'label' => __('docs.menu.blog'), 'desc' => __('docs.menu.blog_desc')],
                    ['icon' => 'fa-hand-holding-usd', 'label' => __('docs.menu.payout_admin'), 'desc' => __('docs.menu.payout_admin_desc')],
                    ['icon' => 'fa-box', 'label' => __('docs.menu.plans'), 'desc' => __('docs.menu.plans_desc')],
                    ['icon' => 'fa-id-card', 'label' => __('docs.menu.subscriptions'), 'desc' => __('docs.menu.subscriptions_desc')],
                    ['icon' => 'fa-key', 'label' => __('docs.menu.api_tokens'), 'desc' => __('docs.menu.api_tokens_desc')],
                    ['icon' => 'fa-wallet', 'label' => __('docs.menu.payout'), 'desc' => __('docs.menu.payout_desc')],
                    ['icon' => 'fa-history', 'label' => __('docs.menu.logs'), 'desc' => __('docs.menu.logs_desc')],
                ]
            ],
        ];
    }

    protected function tutorialPhases(): array
    {
        return [
            [
                'phase' => __('docs.tutorial.phase_1_title'),
                'icon' => 'fa-rocket',
                'steps' => [
                    __('docs.tutorial.step_1_1'),
                    __('docs.tutorial.step_1_2'),
                    __('docs.tutorial.step_1_3'),
                    __('docs.tutorial.step_1_4'),
                ]
            ],
            [
                'phase' => __('docs.tutorial.phase_2_title'),
                'icon' => 'fa-plug',
                'steps' => [
                    __('docs.tutorial.step_2_1'),
                    __('docs.tutorial.step_2_2'),
                    __('docs.tutorial.step_2_3'),
                    __('docs.tutorial.step_2_4'),
                ]
            ],
            [
                'phase' => __('docs.tutorial.phase_3_title'),
                'icon' => 'fa-address-book',
                'steps' => [
                    __('docs.tutorial.step_3_1'),
                    __('docs.tutorial.step_3_2'),
                    __('docs.tutorial.step_3_3'),
                    __('docs.tutorial.step_3_4'),
                ]
            ],
            [
                'phase' => __('docs.tutorial.phase_4_title'),
                'icon' => 'fa-robot',
                'steps' => [
                    __('docs.tutorial.step_4_1'),
                    __('docs.tutorial.step_4_2'),
                    __('docs.tutorial.step_4_3'),
                    __('docs.tutorial.step_4_4'),
                    __('docs.tutorial.step_4_5'),
                ]
            ],
            [
                'phase' => __('docs.tutorial.phase_5_title'),
                'icon' => 'fa-bullhorn',
                'steps' => [
                    __('docs.tutorial.step_5_1'),
                    __('docs.tutorial.step_5_2'),
                    __('docs.tutorial.step_5_3'),
                    __('docs.tutorial.step_5_4'),
                    __('docs.tutorial.step_5_5'),
                ]
            ],
            [
                'phase' => __('docs.tutorial.phase_6_title'),
                'icon' => 'fa-comments',
                'steps' => [
                    __('docs.tutorial.step_6_1'),
                    __('docs.tutorial.step_6_2'),
                    __('docs.tutorial.step_6_3'),
                    __('docs.tutorial.step_6_4'),
                ]
            ],
            [
                'phase' => __('docs.tutorial.phase_7_title'),
                'icon' => 'fa-bolt',
                'steps' => [
                    __('docs.tutorial.step_7_1'),
                    __('docs.tutorial.step_7_2'),
                    __('docs.tutorial.step_7_3'),
                    __('docs.tutorial.step_7_4'),
                ]
            ],
        ];
    }

    protected function features(): array
    {
        return [
            [
                'group' => __('docs.features.group_chat'),
                'icon' => 'fa-comments',
                'items' => [
                    ['title' => __('docs.features.chat_omnichannel_title'), 'desc' => __('docs.features.chat_omnichannel_desc')],
                    ['title' => __('docs.features.multi_agent_title'), 'desc' => __('docs.features.multi_agent_desc')],
                    ['title' => __('docs.features.template_title'), 'desc' => __('docs.features.template_desc')],
                ]
            ],
            [
                'group' => __('docs.features.group_automation'),
                'icon' => 'fa-robot',
                'items' => [
                    ['title' => __('docs.features.autoreply_title'), 'desc' => __('docs.features.autoreply_desc')],
                    ['title' => __('docs.features.recurring_title'), 'desc' => __('docs.features.recurring_desc')],
                    ['title' => __('docs.features.campaign_title'), 'desc' => __('docs.features.campaign_desc')],
                ]
            ],
            [
                'group' => __('docs.features.group_contacts'),
                'icon' => 'fa-address-book',
                'items' => [
                    ['title' => __('docs.features.contact_db_title'), 'desc' => __('docs.features.contact_db_desc')],
                    ['title' => __('docs.features.contact_group_title'), 'desc' => __('docs.features.contact_group_desc')],
                    ['title' => __('docs.features.import_csv_title'), 'desc' => __('docs.features.import_csv_desc')],
                ]
            ],
            [
                'group' => __('docs.features.group_integration'),
                'icon' => 'fa-plug',
                'items' => [
                    ['title' => __('docs.features.webhook_title'), 'desc' => __('docs.features.webhook_desc')],
                    ['title' => __('docs.features.api_token_title'), 'desc' => __('docs.features.api_token_desc')],
                    ['title' => __('docs.features.ai_keys_title'), 'desc' => __('docs.features.ai_keys_desc')],
                ]
            ],
            [
                'group' => __('docs.features.group_management'),
                'icon' => 'fa-cog',
                'items' => [
                    ['title' => __('docs.features.dashboard_title'), 'desc' => __('docs.features.dashboard_desc')],
                    ['title' => __('docs.features.plans_title'), 'desc' => __('docs.features.plans_desc')],
                    ['title' => __('docs.features.payout_title'), 'desc' => __('docs.features.payout_desc')],
                ]
            ],
        ];
    }
}
