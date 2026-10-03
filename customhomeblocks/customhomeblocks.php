<?php
/**
 * Custom Home Blocks
 *
 * @author    Oscar Periche <info@metalinked.net>
 * @copyright 2026 Oscar Periche
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2 or later (GPL-2.0+)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Customhomeblocks extends Module
{
    const CONFIG_KEY = 'CUSTOMHOMEBLOCKS_BLOCKS';

    private ?array $cachedBlocks = null;

    public function __construct()
    {
        $this->name          = 'customhomeblocks';
        $this->tab           = 'front_office_features';
        $this->version       = '1.0.4';
        $this->author        = 'Oscar Periche';
        $this->need_instance = 0;
        $this->bootstrap     = true;
        $this->allow_push    = true;

        parent::__construct();

        $this->displayName = $this->l('Custom Home Blocks');
        $this->description = $this->l('Add custom HTML content blocks to the homepage.');

        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    /* ------------------------------------------------------------------ */
    /*  Install / Uninstall                                                  */
    /* ------------------------------------------------------------------ */

    public function install()
    {
        $this->saveBlocks([]);

        return parent::install()
            && $this->registerHook('displayHome');
    }

    public function uninstall()
    {
        Configuration::deleteByName(self::CONFIG_KEY);

        return parent::uninstall();
    }

    /* ------------------------------------------------------------------ */
    /*  Block data helpers                                                   */
    /* ------------------------------------------------------------------ */

    private function getBlocks(): array
    {
        if ($this->cachedBlocks === null) {
            $raw                = Configuration::get(self::CONFIG_KEY);
            $decoded            = json_decode(base64_decode((string) $raw), true);
            $this->cachedBlocks = is_array($decoded) ? $decoded : [];
        }

        return $this->cachedBlocks;
    }

    private function saveBlocks(array $blocks): void
    {
        $this->cachedBlocks = array_values($blocks);
        Configuration::updateValue(self::CONFIG_KEY, base64_encode(json_encode($this->cachedBlocks)));
    }

    /* ------------------------------------------------------------------ */
    /*  Back-office configuration                                            */
    /* ------------------------------------------------------------------ */

    private function getConfigureBaseUrl(): string
    {
        $scriptPath = explode('?', $_SERVER['REQUEST_URI'])[0];
        $protocol   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

        return $protocol . '://' . $_SERVER['HTTP_HOST']
            . $scriptPath
            . '?controller=AdminModules'
            . '&configure=' . $this->name
            . '&token=' . Tools::getAdminTokenLite('AdminModules');
    }

    public function getContent()
    {
        $output = '';
        $action = Tools::getValue('action', 'list');

        // POST: save block — hidden field detects submission reliably in PS9
        if (isset($_POST['customhomeblocks_save'])) {
            $blockId = trim($_POST['block_id'] ?? '');
            $title   = strip_tags(trim($_POST['block_title'] ?? ''));
            $html    = $_POST['block_html'] ?? '';

            if ($title === '') {
                $output .= $this->displayError($this->l('Block title cannot be empty.'));
                return $output . $this->renderBlockForm($blockId ? 'edit' : 'add');
            }

            $blocks = $this->getBlocks();

            if ($blockId) {
                foreach ($blocks as &$block) {
                    if ($block['id'] === $blockId) {
                        $block['title'] = $title;
                        $block['html']  = $html;
                        break;
                    }
                }
                unset($block);
            } else {
                $blocks[] = [
                    'id'      => uniqid('block_', true),
                    'title'   => $title,
                    'html'    => $html,
                    'enabled' => true,
                ];
            }

            $this->saveBlocks($blocks);
            $output .= $this->displayConfirmation($this->l('Block saved successfully.'));
            $action = 'list';
        }

        // GET: delete
        if ($action === 'delete') {
            $blockId = Tools::getValue('block_id');
            $this->saveBlocks(array_filter($this->getBlocks(), fn($b) => $b['id'] !== $blockId));
            $output .= $this->displayConfirmation($this->l('Block deleted.'));
            $action = 'list';
        }

        // GET: toggle enabled/disabled
        if ($action === 'toggle') {
            $blockId = Tools::getValue('block_id');
            $blocks  = $this->getBlocks();
            foreach ($blocks as &$block) {
                if ($block['id'] === $blockId) {
                    $block['enabled'] = !($block['enabled'] ?? true);
                    break;
                }
            }
            unset($block);
            $this->saveBlocks($blocks);
            $action = 'list';
        }

        // GET: move up/down
        if ($action === 'move') {
            $blockId   = Tools::getValue('block_id');
            $direction = Tools::getValue('direction');
            $blocks    = $this->getBlocks();

            foreach ($blocks as $i => $b) {
                if ($b['id'] === $blockId) {
                    $swap = ($direction === 'up') ? $i - 1 : $i + 1;
                    if (isset($blocks[$swap])) {
                        [$blocks[$i], $blocks[$swap]] = [$blocks[$swap], $blocks[$i]];
                    }
                    break;
                }
            }

            $this->saveBlocks($blocks);
            $action = 'list';
        }

        if (in_array($action, ['add', 'edit'])) {
            return $output . $this->renderBlockForm($action);
        }

        return $output . $this->renderBlockList();
    }

    private function renderBlockList(): string
    {
        $blocks  = $this->getBlocks();
        $baseUrl = $this->getConfigureBaseUrl();
        $total   = count($blocks);

        $html  = '<div class="panel">';
        $html .= '<div class="panel-heading">'
               . '<i class="icon-list"></i> ' . $this->l('Content Blocks')
               . ' <span class="badge">' . $total . '</span>'
               . '</div>';
        $html .= '<div class="panel-body">';

        if (empty($blocks)) {
            $html .= '<div class="text-center" style="padding:40px 0">'
                   . '<i class="icon-columns" style="font-size:48px;color:#ccc;display:block;margin-bottom:16px"></i>'
                   . '<p class="text-muted" style="font-size:15px;margin-bottom:20px">'
                   . $this->l('No blocks yet. Create your first one to start adding content to the homepage.')
                   . '</p>'
                   . '<a href="' . $baseUrl . '&action=add" class="btn btn-primary btn-lg">'
                   . '<i class="icon-plus"></i> ' . $this->l('Add your first block') . '</a>'
                   . '</div>';
        } else {
            $html .= '<table class="table table-striped">'
                   . '<thead><tr>'
                   . '<th style="width:40px">#</th>'
                   . '<th>' . $this->l('Title') . '</th>'
                   . '<th>' . $this->l('Content preview') . '</th>'
                   . '<th style="width:90px;text-align:center">' . $this->l('Status') . '</th>'
                   . '<th style="width:70px;text-align:center">' . $this->l('Order') . '</th>'
                   . '<th style="width:160px">' . $this->l('Actions') . '</th>'
                   . '</tr></thead><tbody>';

            foreach ($blocks as $i => $block) {
                $enabled   = $block['enabled'] ?? true;
                $editUrl   = $baseUrl . '&action=edit&block_id='             . urlencode($block['id']);
                $deleteUrl = $baseUrl . '&action=delete&block_id='           . urlencode($block['id']);
                $toggleUrl = $baseUrl . '&action=toggle&block_id='           . urlencode($block['id']);
                $upUrl     = $baseUrl . '&action=move&direction=up&block_id='   . urlencode($block['id']);
                $downUrl   = $baseUrl . '&action=move&direction=down&block_id=' . urlencode($block['id']);

                $stripped = strip_tags($block['html']);
                $preview  = mb_substr($stripped, 0, 90, 'UTF-8');
                if (mb_strlen($stripped, 'UTF-8') > 90) {
                    $preview .= '…';
                }

                $rowStyle = $enabled ? '' : ' style="opacity:.55"';
                $html .= '<tr' . $rowStyle . '>';
                $html .= '<td>' . ($i + 1) . '</td>';
                $html .= '<td><strong>' . htmlspecialchars($block['title'], ENT_QUOTES, 'UTF-8') . '</strong></td>';
                $html .= '<td style="font-size:12px;color:#888;max-width:300px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">'
                       . htmlspecialchars($preview !== '' ? $preview : '—', ENT_QUOTES, 'UTF-8')
                       . '</td>';

                $html .= '<td style="text-align:center">';
                if ($enabled) {
                    $html .= '<a href="' . $toggleUrl . '" class="btn btn-xs btn-success" title="' . $this->l('Click to disable') . '">'
                           . '<i class="icon-check"></i> ' . $this->l('Active') . '</a>';
                } else {
                    $html .= '<a href="' . $toggleUrl . '" class="btn btn-xs btn-default" title="' . $this->l('Click to enable') . '">'
                           . '<i class="icon-minus-sign"></i> ' . $this->l('Disabled') . '</a>';
                }
                $html .= '</td>';

                $html .= '<td style="text-align:center;white-space:nowrap">';
                if ($i > 0) {
                    $html .= '<a href="' . $upUrl . '" class="btn btn-default btn-xs" title="' . $this->l('Move up') . '">'
                           . '<i class="icon-arrow-up"></i></a> ';
                }
                if ($i < $total - 1) {
                    $html .= '<a href="' . $downUrl . '" class="btn btn-default btn-xs" title="' . $this->l('Move down') . '">'
                           . '<i class="icon-arrow-down"></i></a>';
                }
                $html .= '</td>';

                $html .= '<td style="white-space:nowrap">';
                $html .= '<a href="' . $editUrl . '" class="btn btn-default btn-sm">'
                       . '<i class="icon-pencil"></i> ' . $this->l('Edit') . '</a> ';
                $html .= '<a href="' . $deleteUrl . '" class="btn btn-danger btn-sm"'
                       . ' onclick="return confirm(\'' . addslashes($this->l('Delete this block?')) . '\')">'
                       . '<i class="icon-trash"></i> ' . $this->l('Delete') . '</a>';
                $html .= '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
        }

        $html .= '</div>';
        if (!empty($blocks)) {
            $html .= '<div class="panel-footer">';
            $html .= '<a href="' . $baseUrl . '&action=add" class="btn btn-primary">'
                   . '<i class="icon-plus"></i> ' . $this->l('Add block') . '</a>';
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    private function renderBlockForm(string $action): string
    {
        $blockId = Tools::getValue('block_id', '');
        $block   = ['id' => '', 'title' => '', 'html' => '', 'enabled' => true];

        if ($action === 'edit' && $blockId) {
            foreach ($this->getBlocks() as $b) {
                if ($b['id'] === $blockId) {
                    $block = $b;
                    break;
                }
            }
        }

        $baseUrl = $this->getConfigureBaseUrl();
        $legend  = $action === 'edit' ? $this->l('Edit Block') : $this->l('Add Block');

        $html  = '<form action="' . $baseUrl . '" method="post">';
        $html .= '<div class="panel">';
        $html .= '<div class="panel-heading"><i class="icon-pencil"></i> ' . $legend . '</div>';
        $html .= '<div class="panel-body">';
        $html .= '<input type="hidden" name="customhomeblocks_save" value="1">';
        $html .= '<input type="hidden" name="block_id" value="' . htmlspecialchars($block['id'], ENT_QUOTES, 'UTF-8') . '">';

        $html .= '<div class="form-group" style="margin-bottom:20px;overflow:hidden">';
        $html .= '<label class="control-label col-lg-3 required">' . $this->l('Block title') . '</label>';
        $html .= '<div class="col-lg-9">';
        $html .= '<input type="text" name="block_title" class="form-control" required'
               . ' placeholder="' . $this->l('e.g. Summer banner, Promo text...') . '"'
               . ' value="' . htmlspecialchars($block['title'], ENT_QUOTES, 'UTF-8') . '">';
        $html .= '<p class="help-block">' . $this->l('Internal label — not visible on the front office.') . '</p>';
        $html .= '</div></div>';

        $html .= '<div class="form-group" style="margin-top:20px">';
        $html .= '<label class="control-label col-lg-3">' . $this->l('HTML content') . '</label>';
        $html .= '<div class="col-lg-9">';
        $html .= '<textarea name="block_html" rows="20" class="form-control"'
               . ' style="font-family:monospace;font-size:13px;resize:vertical"'
               . ' placeholder="' . htmlspecialchars('<section class="my-block">&#10;  ...&#10;</section>', ENT_QUOTES, 'UTF-8') . '">';
        $html .= htmlspecialchars($block['html'], ENT_QUOTES, 'UTF-8');
        $html .= '</textarea>';
        $html .= '<p class="help-block">'
               . $this->l('Raw HTML — all tags, attributes, inline styles and scripts are preserved exactly as written.')
               . '</p>';
        $html .= '</div></div>';

        $html .= '</div>';
        $html .= '<div class="panel-footer">';
        $html .= '<button type="submit" class="btn btn-primary"><i class="process-icon-save"></i> ' . $this->l('Save') . '</button> ';
        $html .= '<a href="' . $baseUrl . '" class="btn btn-default"><i class="process-icon-cancel"></i> ' . $this->l('Cancel') . '</a>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</form>';

        return $html;
    }

    /* ------------------------------------------------------------------ */
    /*  Front-office hook                                                    */
    /* ------------------------------------------------------------------ */

    public function hookDisplayHome($params)
    {
        $blocks = array_values(array_filter(
            $this->getBlocks(),
            fn($b) => $b['enabled'] ?? true
        ));

        if (empty($blocks)) {
            return '';
        }

        $this->context->smarty->assign(['blocks' => $blocks]);

        return $this->display(__FILE__, 'views/templates/hook/block.tpl');
    }
}
