<?php
declare(strict_types=1);

namespace App\Tools\teemip\get_webservices_version;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Schema\ToolAnnotations;
use Twig\Environment;
use App\Service\iTopClientInterface;
use App\Service\DatamodelService;
use Psr\Log\LoggerInterface;
use App\Tools\iTopRestTools;

class TipGetWebservicesVersionFileTools extends iTopRestTools
{
    private DatamodelService $datamodel;

    public function __construct(Environment $twig, iTopClientInterface $iTopClient, LoggerInterface $mcpLogger, DatamodelService $datamodel)
    {
        $this->datamodel = $datamodel;
        parent::__construct($twig, $iTopClient, $mcpLogger);
    }

    /**
     * This tool get the verson of the teemIP web services
     *
     */
    #[McpTool(name: TOOL_PREFIX.'get-webservices-version', annotations: new ToolAnnotations(null, true, false, true, false))]
    public function getWebServicesVersion(): string
    {
        $this->mcpLogger->info('[Tool called] get-webservices-version');
        return $this->runToolFromTemplates('tipGetWebServicesVersion', 'Anything', []);
    }
}
