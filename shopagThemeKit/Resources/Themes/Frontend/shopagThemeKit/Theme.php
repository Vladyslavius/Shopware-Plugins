<?php

namespace Shopware\Themes\shopagThemeKit;

use Shopware\Components\Form as Form;

class Theme extends \Shopware\Components\Theme
{
    protected $extend = 'Responsive';

    protected $name = <<<'SHOPWARE_EOD'
shopagThemeKit
SHOPWARE_EOD;

    protected $description = <<<'SHOPWARE_EOD'

SHOPWARE_EOD;

    protected $author = <<<'SHOPWARE_EOD'

SHOPWARE_EOD;

    protected $license = <<<'SHOPWARE_EOD'

SHOPWARE_EOD;

    public function createConfig(Form\Container\TabContainer $container)
    {
        //General settings
        $shopagSettingsGeneral = $this->createFieldSet(
            'shopag_settings_general',
            'General settings',
            [
                'attributes' => [
                    'padding' => '10',
                    'margin' => '5',
                    'layout' => 'anchor',
                    'defaults' => ['labelWidth' => 220, 'anchor' => '100%'],
                ],
            ]
        );
        $shopagSettingsGeneral->addElement(
            $this->createColorPickerField(
                'shopag-body-background',
                'Body background',
                '#ececec'
            )
        );
        $shopagSettingsGeneral->addElement(
            $this->createColorPickerField(
                'shopag-header-background',
                'Header background',
                '#ffffff'
            )
        );
        $shopagSettingsGeneral->addElement(
            $this->createColorPickerField(
                'shopag-main-navigation-background',
                'Main navigation background',
                '#ffffff'
            )
        );
        $shopagSettingsGeneral->addElement(
            $this->createTextField(
                'shopag-main-navigation-margin',
                'Main navigation margin',
                '0px 0px 0px 0px'
            )
        );
        $shopagSettingsGeneral->addElement(
            $this->createTextField(
                'shopag-main-navigation-padding',
                'Main navigation padding',
                '0px 0px 0px 0px'
            )
        );
        $shopagSettingsGeneral->addElement(
            $this->createColorPickerField(
                'shopag-footer-background',
                'Footer background',
                '#ececec'
            )
        );
        $shopagSettingsGeneral->addElement(
            $this->createColorPickerField(
                'shopag-listing-product-box-background',
                'Listing product box background',
                '#ffffff'
            )
        );
        $shopagSettingsGeneral->addElement(
            $this->createTextField(
                'shopag-listing-product-box-margin',
                'Listing product box margin',
                '0px 0px 0px 0px'
            )
        );
        $shopagSettingsGeneral->addElement(
            $this->createTextField(
                'shopag-listing-product-box-padding',
                'Listing product box padding',
                '0px 0px 0px 0px'
            )
        );
        $shopagSettingsGeneral->addElement(
            $this->createTextField(
                'shopag-border-setting-width',
                'Border width',
                '1px'
            )
        );
        $shopagSettingsGeneral->addElement(
            $this->createColorPickerField(
                'shopag-border-setting-color',
                'Border color',
                '#dadae5'
            )
        );
        $shopagSettingsGeneral->addElement(
            $this->createTextField(
                'shopag-border-setting-style',
                'Border style',
                'solid'
            )
        );
        $shopagSettingsGeneral->addElement(
            $this->createTextField(
                'shopag-border-setting-radius',
                'Border radius',
                '3px'
            )
        );


        //Template mode
        $shopagTemplateMode = $this->createFieldSet(
            'shopag_template_mode',
            'Template mode',
            [
                'attributes' => [
                    'padding' => '10',
                    'margin' => '5',
                    'layout' => 'anchor',
                    'defaults' => ['labelWidth' => 220, 'anchor' => '100%'],
                ],
            ]
        );
        $shopagTemplateMode->addElement(
            $this->createSelectField(
                'shopag-template-mode-value',
                'Mode',
                0,
                [
                    ['value' => 0, 'text' => 'Full-Width'],
                    ['value' => 1, 'text' => 'Boxed'],
                    ['value' => 2, 'text' => 'Full-Width + Boxed'],
                ]
            )
        );
        $shopagTemplateMode->addElement(
            $this->createTextField(
                'shopag-template-mode-width-px',
                'Width (px)',
                '1260px'
            )
        );
        $shopagTemplateMode->addElement(
            $this->createTextField(
                'shopag-template-mode-width-percent',
                'Width (percent)',
                '90%'
            )
        );


        //Different Iconsets
        $shopagDifferentIconsets = $this->createFieldSet(
            'shopag_different_iconsets',
            'Different Icon sets',
            [
                'attributes' => [
                    'padding' => '10',
                    'margin' => '5',
                    'layout' => 'anchor',
                    'defaults' => ['labelWidth' => 220, 'anchor' => '100%'],
                ],
            ]
        );
        $shopagDifferentIconsets->addElement(
            $this->createSelectField(
                'shopag-different-iconsets-value',
                'Iconset',
                0,
                [
                    ['value' => 0, 'text' => 'Default'],
                    ['value' => 'emz-icons', 'text' => 'Custom icons']
                ]
            )
        );

        //Google Webfonts
        $shopagGoogleWebfonts = $this->createFieldSet(
            'shopag_google_webfonts',
            'Google Webfonts',
            [
                'attributes' => [
                    'padding' => '10',
                    'margin' => '5',
                    'layout' => 'anchor',
                    'defaults' => ['labelWidth' => 220, 'anchor' => '100%'],
                ],
            ]
        );
        $shopagGoogleWebfonts->addElement(
            $this->createTextAreaField(
                'shopag_google_webfonts_links',
                'Links',
                '',
                ['attributes' => ['xtype' => 'textarea', 'lessCompatible' => false], 'help' => '__additional_css_data_description__']
            )
        );
        $shopagGoogleWebfonts->addElement(
            $this->createCheckboxField(
                'shopag-google-webfonts-h1h6-allow',
                'Enable custom font style for h1-h6',
                false
            )
        );
        $shopagGoogleWebfonts->addElement(
            $this->createTextField(
                'shopag-google-webfonts-h1h6-fontfamily',
                'H1-H6 font-family',
                ''
            )
        );
        $shopagGoogleWebfonts->addElement(
            $this->createCheckboxField(
                'shopag-google-webfonts-body-allow',
                'Enable custom font style for body',
                false
            )
        );
        $shopagGoogleWebfonts->addElement(
            $this->createTextField(
                'shopag-google-webfonts-body-fontfamily',
                'Body font-family',
                ''
            )
        );

        //Settings for Header
        $shopagSettingsHeader = $this->createFieldSet(
            'shopag_settings_header',
            'Settings for Header',
            [
                'attributes' => [
                    'padding' => '10',
                    'margin' => '5',
                    'layout' => 'anchor',
                    'defaults' => ['labelWidth' => 220, 'anchor' => '100%'],
                ],
            ]
        );
        $shopagSettingsHeader->addElement(
            $this->createSelectField(
                'shopag_settings_header_stick',
                'Type',
                0,
                [
                    ['value' => 0, 'text' => 'Default'],
                    ['value' => 'header-stick', 'text' => 'Header sticky'],
                    ['value' => 'header-stick-animate', 'text' => 'Header sticky + animate']
                ]
            )
        );
        $shopagSettingsHeader->addElement(
            $this->createSelectField(
                'shopag_settings_header_template',
                'Template',
                'default',
                [
                    ['value' => 'default', 'text' => 'Default'],
                    ['value' => 'header-template-ll-plus-nav', 'text' => 'Logo left + nav'],
                    ['value' => 'header-template-ll-nav', 'text' => 'Logo left - nav'],
                    ['value' => 'header-template-lc-plus-nav', 'text' => 'Logo center + nav'],
                    ['value' => 'header-template-lc-nav', 'text' => 'Logo center - nav']

                ]
            )
        );
        $shopagSettingsHeader->addElement(
            $this->createSelectField(
                'shopag-settings-header-main-navigation-align-items',
                'Align main-navigation items',
                'left',
                [
                    ['value' => 'left', 'text' => 'left'],
                    ['value' => 'center', 'text' => 'center'],
                    ['value' => 'right', 'text' => 'right']
                ]
            )
        );


        //Google Webfonts
        $shopagHomeIcon = $this->createFieldSet(
            'shopag_home_icon',
            'Home-icon',
            [
                'attributes' => [
                    'padding' => '10',
                    'margin' => '5',
                    'layout' => 'anchor',
                    'defaults' => ['labelWidth' => 220, 'anchor' => '100%'],
                ],
            ]
        );
        $shopagHomeIcon->addElement(
            $this->createCheckboxField(
                'shopag_home_icon_allow',
                'Active',
                false
            )
        );



        //Settings Topbar advantages
        $shopagTopbarAdvantages = $this->createFieldSet(
            'shopag_settings_header_advantages',
            'Top Bar benefits',
            [
                'attributes' => [
                    'padding' => '10',
                    'margin' => '5',
                    'layout' => 'anchor',
                    'defaults' => ['labelWidth' => 220, 'anchor' => '100%'],
                ],
            ]
        );
        $shopagTopbarAdvantages->addElement(
            $this->createCheckboxField(
                'shopag_settings_header_advantages_allow',
                'Active',
                false
            )
        );
        $shopagTopbarAdvantages->addElement(
            $this->createSelectField(
                'shopag_settings_header_advantages_position',
                'Position',
                'top',
                [
                    ['value' => 'top', 'text' => 'Top'],
                    ['value' => 'center', 'text' => 'Center'],
                    ['value' => 'bottom', 'text' => 'Bottom']
                ]
            )
        );
        $shopagTopbarAdvantages->addElement(
            $this->createColorPickerField(
                'shopag-settings-header-advantages-background',
                'Background',
                '#ececec'
            )
        );
        $shopagTopbarAdvantages->addElement(
            $this->createColorPickerField(
                'shopag-settings-header-advantages-color',
                'Color',
                '#000'
            )
        );
        $shopagTopbarAdvantages->addElement(
            $this->createSelectField(
                'shopag-settings-header-advantages-justifycontent',
                'Justify content',
                'center',
                [
                    ['value' => 'center', 'text' => 'center'],
                    ['value' => 'flex-end', 'text' => 'flex-end'],
                    ['value' => 'flex-start', 'text' => 'flex-start'],
                    ['value' => 'space-around', 'text' => 'space-around'],
                    ['value' => 'space-between', 'text' => 'space-between']
                ]
            )
        );
        $shopagTopbarAdvantages->addElement(
            $this->createTextField(
                'shopag_settings_header_advantages_item_1',
                'Item 1',
                '<i class="icon--truck"></i> Lorem Ipsum ist ein',
                ['attributes' => ['xtype' => 'textarea', 'lessCompatible' => false], 'help' => '__additional_css_data_description__']
            )
        );
        $shopagTopbarAdvantages->addElement(
            $this->createTextField(
                'shopag_settings_header_advantages_item_2',
                'Item 2',
                '',
                ['attributes' => ['xtype' => 'textarea', 'lessCompatible' => false], 'help' => '__additional_css_data_description__']
            )
        );
        $shopagTopbarAdvantages->addElement(
            $this->createTextField(
                'shopag_settings_header_advantages_item_3',
                'Item 3',
                '',
                ['attributes' => ['xtype' => 'textarea', 'lessCompatible' => false], 'help' => '__additional_css_data_description__']
            )
        );
        $shopagTopbarAdvantages->addElement(
            $this->createTextField(
                'shopag_settings_header_advantages_item_4',
                'Item 4',
                '',
                ['attributes' => ['xtype' => 'textarea', 'lessCompatible' => false], 'help' => '__additional_css_data_description__']
            )
        );
        $shopagTopbarAdvantages->addElement(
            $this->createTextField(
                'shopag_settings_header_advantages_item_5',
                'Item 5',
                '',
                ['attributes' => ['xtype' => 'textarea', 'lessCompatible' => false], 'help' => '__additional_css_data_description__']
            )
        );


        //Create tab
        $tab = $this->createTab(
            'shopag_settings',
            'Settings',
            [
                'attributes' => [
                    'layout' => 'anchor',
                    'autoScroll' => true,
                    'padding' => '0',
                    'defaults' => ['anchor' => '100%'],
                ],
            ]
        );

        $tab->addElement($shopagSettingsGeneral);
        $tab->addElement($shopagTemplateMode);
        $tab->addElement($shopagDifferentIconsets);
        $tab->addElement($shopagGoogleWebfonts);
        $tab->addElement($shopagSettingsHeader);
        $tab->addElement($shopagHomeIcon);
        $tab->addElement($shopagTopbarAdvantages);

        $container->addTab($tab);
    }

    protected $javascript = [
        'src/js/scripts.js'
    ];

}