<?php
declare(strict_types=1);

namespace App\Tools\teemip\pick_subnet_in_block;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Schema\ToolAnnotations;
use Twig\Environment;
use App\Service\iTopClientInterface;
use Psr\Log\LoggerInterface;
use App\Tools\iTopRestTools;

class TipPickSubnetInBlockTools extends iTopRestTools
{
    public function __construct(Environment $twig, iTopClientInterface $iTopClient, LoggerInterface $mcpLogger)
    {
        parent::__construct($twig, $iTopClient, $mcpLogger);
    }

    /**
     * This tool looks for and creates a subnet in a given block. Block may be an IPv4 or an IPv6 one.
     * @param int $block_id The key of the block where to create the subnet
     * @param string $fields A JSON formatted object {"field_code":"field_value"} for the fields to set into the new subnet
     */
    #[McpTool(name: TOOL_PREFIX.'pick_subnet_in_block', annotations: new ToolAnnotations(null, false, true, false, false))]
    public function pickSubnetInBlock(int $block_id, string $fields_json): string
    {
        if ($block_id < 0) {
            $error = 'Error: invalid value for the parameter "$block_id". Expecting the key of the block to consider.';
            $this->mcpLogger->error('[Tool called] pick_subnet_in_block', ['error' => $error, 'object_class' => 'IPBlock', 'object_key' => $block_id, 'fields_json' => $fields_json]);
            return $error;
        }
        $fields = json_decode($fields_json, true);
        if ($fields === false) {
            $error = 'Error: invalid value for the parameter "field_json". Expecting a valid JSON structure {"code":"value"} for each field to populate.';
            $this->mcpLogger->error('[Tool called] pick_subnet_in_block', ['error' => $error, 'object_class' => 'IPBlock', 'object_key' => $block_id, 'fields_json' => $fields_json]);
            return $error;
        }
        $blockClass = $this->getBlockFinalClass($block_id);

        $this->mcpLogger->info('[Tool called] pick_subnet_in_block');
        return $this->runToolFromTemplates('tipPickSubnetInBlock', 'Anything',['key' => $block_id, 'class' => $blockClass, 'fields' => $fields]
        );
    }

    /**
     * Get the finalclass of a subnet identified by its key,
     * @param int $block_id The key of the subnet
     * @return string Final class of the subnet
     */
    private function getBlockFinalClass(int $block_id): string
    {
        $json = $this->twig->render('getAnythingDetails-input.json.twig', ['class' => 'IPBlock', 'key' => $block_id]);
        $response = json_decode($this->postJsonToItop($json), true);

        foreach (($response['objects'] ?? []) as $object) {
            return $object['fields']['finalclass'] ?? $object['class'] ?? 'IPBlock';
        }

        return 'IPBlock';
    }
}
