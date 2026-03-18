<?php
/**
 * Custom Home Blocks
 *
 * @author    Oscar Periche <info@metalinked.net>
 * @copyright 2026 Oscar Periche
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Customhomeblocks extends Module
{
    const CONFIG_KEY = 'CUSTOMHOMEBLOCKS_BLOCKS';

    public function __construct()
    {
        $this->name          = 'customhomeblocks';
        $this->tab           = 'front_office_features';
        $this->version       = '1.0.0';
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
        Configuration::updateValue(self::CONFIG_KEY, json_encode([]));

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
        $raw    = Configuration::get(self::CONFIG_KEY);
        $blocks = json_decode(base64_decode((string) $raw), true);

        return is_array($blocks) ? $blocks : [];
    }

    private function saveBlocks(array $blocks): void
    {
        Configuration::updateValue(self::CONFIG_KEY, base64_encode(json_encode(array_values($blocks))));
    }

    /* ------------------------------------------------------------------ */
    /*  Back-office configuration                                            */
    /* ------------------------------------------------------------------ */

    private function getConfigureBaseUrl(bool $withToken = true): string
    {
        $scriptPath = explode('?', $_SERVER['REQUEST_URI'])[0];
        $protocol   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

        $url = $protocol . '://' . $_SERVER['HTTP_HOST']
            . $scriptPath
            . '?controller=AdminModules'
            . '&configure=' . $this->name;

        if ($withToken) {
            $url .= '&token=' . Tools::getAdminTokenLite('AdminModules');
        }

        return $url;
    }

    public function getContent()
    {
        $output = '';
        $action = Tools::getValue('action', 'list');

        // POST: save block — use hidden field to detect submission reliably in PS9
        if (isset($_POST['customhomeblocks_save'])) {
            $blockId = trim($_POST['block_id'] ?? '');
            $title   = strip_tags(trim($_POST['block_title'] ?? ''));
            $html    = $_POST['block_html'] ?? '';
            $blocks  = $this->getBlocks();

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
                    'id'    => uniqid('block_', true),
                    'title' => $title,
                    'html'  => $html,
                ];
            }

            $this->saveBlocks($blocks);
            $output .= $this->displayConfirmation($this->l('Block saved successfully.'));
            $action = 'list';
        }

        // GET: delete
        if ($action === 'delete') {
            $blockId = Tools::getValue('block_id');
            $blocks  = array_filter($this->getBlocks(), fn($b) => $b['id'] !== $blockId);
            $this->saveBlocks(array_values($blocks));
            $output .= $this->displayConfirmation($this->l('Block deleted.'));
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

    protected function renderBlockList(): string
    {
        $blocks  = $this->getBlocks();
        $baseUrl = $this->getConfigureBaseUrl();

        $html  = '<div class="panel">';
        $html .= '<div class="panel-heading"><i class="icon-list"></i> ' . $this->l('Content Blocks') . '</div>';
        $html .= '<div class="panel-body">';

        if (empty($blocks)) {
            $html .= '<p class="text-muted">' . $this->l('No blocks yet. Click "Add block" to create one.') . '</p>';
        } else {
            $html .= '<table class="table table-striped">'
                   . '<thead><tr>'
                   . '<th>#</th>'
                   . '<th>' . $this->l('Title') . '</th>'
                   . '<th>' . $this->l('Order') . '</th>'
                   . '<th>' . $this->l('Actions') . '</th>'
                   . '</tr></thead><tbody>';

            $count = count($blocks);
            foreach ($blocks as $i => $block) {
                $editUrl   = $baseUrl . '&action=edit&block_id='      . urlencode($block['id']);
                $deleteUrl = $baseUrl . '&action=delete&block_id='    . urlencode($block['id']);
                $upUrl     = $baseUrl . '&action=move&direction=up&block_id='   . urlencode($block['id']);
                $downUrl   = $baseUrl . '&action=move&direction=down&block_id=' . urlencode($block['id']);

                $html .= '<tr>';
                $html .= '<td>' . ($i + 1) . '</td>';
                $html .= '<td>' . htmlspecialchars($block['title'], ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>';
                if ($i > 0) {
                    $html .= '<a href="' . $upUrl . '" class="btn btn-default btn-xs"><i class="icon-arrow-up"></i></a> ';
                }
                if ($i < $count - 1) {
                    $html .= '<a href="' . $downUrl . '" class="btn btn-default btn-xs"><i class="icon-arrow-down"></i></a>';
                }
                $html .= '</td>';
                $html .= '<td>';
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
        $html .= '<div class="panel-footer">';
        $html .= '<a href="' . $baseUrl . '&action=add" class="btn btn-primary">'
               . '<i class="icon-plus"></i> ' . $this->l('Add block') . '</a>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    protected function renderBlockForm(string $action): string
    {
        $blockId = Tools::getValue('block_id', '');
        $block   = ['id' => '', 'title' => '', 'html' => ''];

        if ($action === 'edit' && $blockId) {
            foreach ($this->getBlocks() as $b) {
                if ($b['id'] === $blockId) {
                    $block = $b;
                    break;
                }
            }
        }

        $formAction = $this->getConfigureBaseUrl();
        $cancelUrl  = $this->getConfigureBaseUrl();
        $legend     = $action === 'edit' ? $this->l('Edit Block') : $this->l('Add Block');

        $html  = '<form action="' . $formAction . '" method="post">';
        $html .= '<div class="panel">';
        $html .= '<div class="panel-heading"><i class="icon-pencil"></i> ' . $legend . '</div>';
        $html .= '<div class="panel-body">';
        $html .= '<input type="hidden" name="customhomeblocks_save" value="1">';
        $html .= '<input type="hidden" name="block_id" value="' . htmlspecialchars($block['id'], ENT_QUOTES, 'UTF-8') . '">';

        $html .= '<div class="form-group" style="margin-bottom:20px;overflow:hidden">';
        $html .= '<label class="control-label col-lg-3 required">' . $this->l('Block Title (internal label)') . '</label>';
        $html .= '<div class="col-lg-9">';
        $html .= '<input type="text" name="block_title" class="form-control" required';
        $html .= ' value="' . htmlspecialchars($block['title'], ENT_QUOTES, 'UTF-8') . '">';
        $html .= '</div></div>';

        $html .= '<div class="form-group" style="margin-top:20px">';
        $html .= '<label class="control-label col-lg-3">' . $this->l('Custom HTML Content') . '</label>';
        $html .= '<div class="col-lg-9">';
        $html .= '<textarea name="block_html" rows="20" style="width:100%;font-family:monospace;font-size:13px">';
        $html .= htmlspecialchars($block['html'], ENT_QUOTES, 'UTF-8');
        $html .= '</textarea>';
        $html .= '<p class="help-block">' . $this->l('Enter raw HTML. Tags are preserved as-is.') . '</p>';
        $html .= '</div></div>';

        $html .= '</div>';
        $html .= '<div class="panel-footer">';
        $html .= '<button type="submit" class="btn btn-primary"><i class="process-icon-save"></i> ' . $this->l('Save') . '</button> ';
        $html .= '<a href="' . $cancelUrl . '" class="btn btn-default"><i class="process-icon-cancel"></i> ' . $this->l('Cancel') . '</a>';
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
        $blocks = $this->getBlocks();

        if (empty($blocks)) {
            return '';
        }

        $this->context->smarty->assign(['blocks' => $blocks]);

        return $this->display(__FILE__, 'views/templates/hook/block.tpl');
    }
}
