<?php
declare(strict_types=1);

namespace App\Tools\teemip\get_nb_of_registered_ips_in_subnet;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Schema\ToolAnnotations;
use Twig\Environment;
use App\Service\iTopClientInterface;
use App\Service\DatamodelService;
use Psr\Log\LoggerInterface;
use App\Tools\iTopRestTools;

class TipGetNbOfRegisteredIpsInSubnetTools extends iTopRestTools
{
    private DatamodelService $datamodel;

    public function __construct(Environment $twig, iTopClientInterface $iTopClient, LoggerInterface $mcpLogger, DatamodelService $datamodel)
    {
        $this->datamodel = $datamodel;
        parent::__construct($twig, $iTopClient, $mcpLogger);
    }

    /**
     * This tool gets the number of registered IPs in a subnet.
     * @param int $subnet_id The identifier or key of the subnet to query
     *
     */
    #[McpTool(name: TOOL_PREFIX.'get_nb_of_registered_ips_in_subnet', annotations: new ToolAnnotations(null, true, false, true, false))]
    public function getNbOfRegisteredIpsInSubnet(int $subnet_id): string
    {
        if ($subnet_id < 0) {
            $error = 'Error: invalid value for the parameter "$subnet_id". Expecting the key of the zone to consider.';
            $this->mcpLogger->error('[Tool called] get_nb_of_registered_ips_in_subnet', ['error' => $error, 'object_class' => 'IPSubnet', 'object_key' => $subnet_id]);
            return $error;
        }
        $this->mcpLogger->info('[Tool called] get_nb_of_registered_ips_in_subnet');
        return $this->runToolFromTemplates('tipGetNbOfRegisteredIpsInSubnet', 'tipGetNbOfRegisteredIpsInSubnet', ['key' => $subnet_id]);
    }

}
