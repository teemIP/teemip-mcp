<?php
declare(strict_types=1);

namespace App\Tools\teemip\pick_ip_address_in_range;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Schema\ToolAnnotations;
use Twig\Environment;
use App\Service\iTopClientInterface;
use Psr\Log\LoggerInterface;
use App\Tools\iTopRestTools;

class TipPickIpAddressInRangeTools extends iTopRestTools
{
    public function __construct(Environment $twig, iTopClientInterface $iTopClient, LoggerInterface $mcpLogger)
    {
        parent::__construct($twig, $iTopClient, $mcpLogger);
    }

    /**
     * This tool looks for and creates an IP address in a given range. Range may be an IPv4 or an IPv6 one.
     * @param int $range_id The key of the range where to create the IP address
     * @param string $fields A JSON formatted object {"field_code":"field_value"} for the fields to set into the new IP Address
     */
    #[McpTool(name: TOOL_PREFIX.'pick_ip_address_in_range', annotations: new ToolAnnotations(null, false, true, false, false))]
    public function pickIpAddressInRange(int $range_id, string $fields_json): string
    {
        if ($range_id < 0) {
            $error = 'Error: invalid value for the parameter "$range_id". Expecting the key of the range to consider.';
            $this->mcpLogger->error('[Tool called] pick_ip_address_in_range', ['error' => $error, 'object_class' => 'IPRange', 'object_key' => $range_id, 'fields_json' => $fields_json]);
            return $error;
        }
        $fields = json_decode($fields_json, true);
        if ($fields === false) {
            $error = 'Error: invalid value for the parameter "field_json". Expecting a valid JSON structure {"code":"value"} for each field to populate.';
            $this->mcpLogger->error('[Tool called] pick_ip_address_in_range', ['error' => $error, 'object_class' => 'IPRange', 'object_key' => $range_id, 'fields_json' => $fields_json]);
            return $error;
        }
        $rangeClass = $this->getRangeFinalClass($range_id);

        $this->mcpLogger->info('[Tool called] pick_ip_address_in_range');
        return $this->runToolFromTemplates('tipPickIpAddressInRange', 'Anything',['key' => $range_id, 'class' => $rangeClass, 'fields' => $fields]
        );
    }

    /**
     * Get the finalclass of a range identified by its key,
     * @param int $range_id The key of the range
     * @return string Final class of the range
     */
    private function getRangeFinalClass(int $range_id): string
    {
        $json = $this->twig->render('getAnythingDetails-input.json.twig', ['class' => 'IPRange', 'key' => $range_id]);
        $response = json_decode($this->postJsonToItop($json), true);

        foreach (($response['objects'] ?? []) as $object) {
            return $object['fields']['finalclass'] ?? $object['class'] ?? 'IPRange';
        }

        return 'IPRange';
    }
}
