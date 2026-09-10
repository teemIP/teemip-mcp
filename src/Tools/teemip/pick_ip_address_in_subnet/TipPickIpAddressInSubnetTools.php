<?php
declare(strict_types=1);

namespace App\Tools\teemip\pick_ip_address_in_subnet;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Schema\ToolAnnotations;
use Twig\Environment;
use App\Service\iTopClientInterface;
use Psr\Log\LoggerInterface;
use App\Tools\iTopRestTools;

class TipPickIpAddressInSubnetTools extends iTopRestTools
{
    public function __construct(Environment $twig, iTopClientInterface $iTopClient, LoggerInterface $mcpLogger)
    {
        parent::__construct($twig, $iTopClient, $mcpLogger);
    }

    /**
     * This tool looks for and creates an IP address in a given subnet. Subnet may be an IPv4 or an IPv6 one.
     * @param int $subnet_id The key of the subnet where to create the IP address
     * @param string $fields A JSON formatted object {"field_code":"field_value"} for the fields to set into the new IP Address
     */
    #[McpTool(name: TOOL_PREFIX.'pick_ip_address_in_subnet', annotations: new ToolAnnotations(null, false, true, false, false))]
    public function pickIpAddressInSubnet(int $subnet_id, string $fields_json): string
    {
        if ($subnet_id < 0) {
            $error = 'Error: invalid value for the parameter "$subnet_id". Expecting the key of the zone to consider.';
            $this->mcpLogger->error('[Tool called] pick_ip_address_in_subnet', ['error' => $error, 'object_class' => 'IPSubnet', 'object_key' => $subnet_id, 'fields_json' => $fields_json]);
            return $error;
        }
        $fields = json_decode($fields_json, true);
        if ($fields === false) {
            $error = 'Error: invalid value for the parameter "field_json". Expecting a valid JSON structure {"code":"value"} for each field to populate.';
            $this->mcpLogger->error('[Tool called] pick_ip_address_in_subnet', ['error' => $error, 'object_class' => 'IPSubnet', 'object_key' => $subnet_id, 'fields_json' => $fields_json]);
            return $error;
        }
        $subnetClass = $this->getSubnetFinalClass($subnet_id);

        $this->mcpLogger->info('[Tool called] pick_ip_address_in_subnet');
        return $this->runToolFromTemplates('tipPickIpAddressInSubnet', 'Anything',['key' => $subnet_id, 'class' => $subnetClass, 'fields' => $fields]
        );
    }

    /**
     * Get the finalclass of a subnet identified by its key,
     * @param int $subnet_id The key of the subnet
     * @return string Final class of the subnet
     */
    private function getSubnetFinalClass(int $subnet_id): string
    {
        $json = $this->twig->render('getAnythingDetails-input.json.twig', ['class' => 'IPSubnet', 'key' => $subnet_id]);
        $response = json_decode($this->postJsonToItop($json), true);

        foreach (($response['objects'] ?? []) as $object) {
            return $object['fields']['finalclass'] ?? $object['class'] ?? 'IPSubnet';
        }

        return 'IPSubnet';
    }
}
