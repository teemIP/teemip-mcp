<?php
declare(strict_types=1);

namespace App\Tools\teemip\get_zone_file;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Schema\ToolAnnotations;
use Twig\Environment;
use App\Service\iTopClientInterface;
use Psr\Log\LoggerInterface;
use App\Tools\iTopRestTools;

class TipGetZoneFileTools extends iTopRestTools
{
    public function __construct(Environment $twig, iTopClientInterface $iTopClient, LoggerInterface $mcpLogger)
    {
        parent::__construct($twig, $iTopClient, $mcpLogger);
    }

    /**
     * This tool gets the data file of a zone
     * @param int $zone_id The identifier or key of the zone to get
     * @param string $output_format Format of the output file: either 'sort_by_record' or 'sort_by_char'
     *
     */
    #[McpTool(name: TOOL_PREFIX.'get-zone-file', annotations: new ToolAnnotations(null, true, false, true, false))]
    public function getZoneFile(int $zone_id, string $output_format): string
    {
        if ($zone_id < 0) {
            $error = 'Error: invalid value for the parameter "zone_id". Expecting the key of the zone to consider.';
            $this->mcpLogger->error('[Tool called] get-zone-file', ['error' => $error, 'object_class' => 'Zone', 'object_key' => $zone_id, 'format' => $output_format]);
            return $error;
        }
        if (($output_format != 'sort_by_record') && ($output_format != 'sort_by_char')) {
            $output_format = 'sort_by_record';
        }
        $this->mcpLogger->info('[Tool called] get-zone-file');
        return $this->runToolFromTemplates('tipGetZoneFile', 'tipGetZoneFile', ['key' => $zone_id, 'format' => $output_format]);
    }

}
