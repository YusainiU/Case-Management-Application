<?php

namespace Simplydigital\AIManagement\Authorization\Permissions;

enum AIPermission: string
{
    case CaseAssistantView = 'ai.case_assistant.view';
    case CaseAssistantAnalyze = 'ai.case_assistant.analyze';
    case CaseAssistantApply = 'ai.case_assistant.apply';
}
