<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* @LanguagesManager/getLanguagesSelector.twig */
class __TwigTemplate_b98f0192ca7b3f4cd85f70037eecb4e5 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        yield "<div
    vue-entry=\"LanguagesManager.LanguagesDropdown\"
    token-auth=\"";
        // line 3
        yield \Piwik\piwik_escape_filter($this->env, json_encode(((array_key_exists("token_auth", $context)) ? (Twig\Extension\CoreExtension::default((isset($context["token_auth"]) || array_key_exists("token_auth", $context) ? $context["token_auth"] : (function () { throw new RuntimeError('Variable "token_auth" does not exist.', 3, $this->source); })()), null)) : (null))), "html", null, true);
        yield "\"
    form-nonce=\"";
        // line 4
        yield \Piwik\piwik_escape_filter($this->env, json_encode((isset($context["nonce"]) || array_key_exists("nonce", $context) ? $context["nonce"] : (function () { throw new RuntimeError('Variable "nonce" does not exist.', 4, $this->source); })())), "html", null, true);
        yield "\"
    languages=\"";
        // line 5
        yield \Piwik\piwik_escape_filter($this->env, json_encode((isset($context["languages"]) || array_key_exists("languages", $context) ? $context["languages"] : (function () { throw new RuntimeError('Variable "languages" does not exist.', 5, $this->source); })())), "html", null, true);
        yield "\"
    current-language-code=\"";
        // line 6
        yield \Piwik\piwik_escape_filter($this->env, json_encode((isset($context["currentLanguageCode"]) || array_key_exists("currentLanguageCode", $context) ? $context["currentLanguageCode"] : (function () { throw new RuntimeError('Variable "currentLanguageCode" does not exist.', 6, $this->source); })())), "html", null, true);
        yield "\"
    current-language-name=\"";
        // line 7
        yield \Piwik\piwik_escape_filter($this->env, json_encode((isset($context["currentLanguageName"]) || array_key_exists("currentLanguageName", $context) ? $context["currentLanguageName"] : (function () { throw new RuntimeError('Variable "currentLanguageName" does not exist.', 7, $this->source); })())), "html", null, true);
        yield "\"
></div>
";
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "@LanguagesManager/getLanguagesSelector.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable()
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  58 => 7,  54 => 6,  50 => 5,  46 => 4,  42 => 3,  38 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<div
    vue-entry=\"LanguagesManager.LanguagesDropdown\"
    token-auth=\"{{ token_auth|default(null)|json_encode }}\"
    form-nonce=\"{{ nonce|json_encode }}\"
    languages=\"{{ languages|json_encode }}\"
    current-language-code=\"{{ currentLanguageCode|json_encode }}\"
    current-language-name=\"{{ currentLanguageName|json_encode }}\"
></div>
", "@LanguagesManager/getLanguagesSelector.twig", "/var/www/html/plugins/LanguagesManager/templates/getLanguagesSelector.twig");
    }
}
