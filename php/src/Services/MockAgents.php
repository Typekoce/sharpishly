namespace App\Services\Agents;

use App\Interfaces\AgentInterface;

class MockClaude implements AgentInterface {
    public function ask(string $p, string $s = ""): string {
        return "Claude (Mock): Analyzing CRM data with nuance and poetic precision.";
    }
}

class MockGrok implements AgentInterface {
    public function ask(string $p, string $s = ""): string {
        return "Grok (Mock): Real-time analysis complete. No cap, the data looks lit.";
    }
}

class MockChatGPT implements AgentInterface {
    public function ask(string $p, string $s = ""): string {
        return "ChatGPT (Mock): As an AI language model, I've processed your request.";
    }
}
