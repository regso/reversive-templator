<?php

namespace app\models\DummyTemplate;

/**
 *  Шаблонизатор, который на основе строки результата и шаблона восстанавливает переменные.
 */
final class DummyTemplate
{
    /**
     * @var string открывающий тег переменной шаблона
     */
    public const OPEN_TAG = '{';

    /**
     * @var string закрывающий тег переменной шаблона
     */
    public const CLOSE_TAG = '}';

    /**
     * @var string результат, образовавшийся в результате шаблонизации
     */
    private string $result;

    /**
     * @var string  шаблон, содержащий 0 и более переменных для шаблонизации
     */
    private string $template;

    /**
     * @var array массив переменных и значений подлежащих шаблонизации (подстановке в шаблон)
     */
    private array  $params;

    /**
     * @param string $result
     * @param string $template
     * @param array  $params
     */
    private function __construct(string $result, string $template, array $params)
    {
        $this->result   = $result;
        $this->template = $template;
        $this->params   = $params;
    }

    /**
     * @param string $result
     * @param $template
     * @return static
     * @throws InvalidTemplateException
     * @throws ResultTemplateMismatchException
     */
    public static function fromResult(string $result, $template): self
    {
        self::validate($template);

        $quotedTemplate = '/^' . preg_quote($template) . '$/';
        $pattern = '/(\\\\' . preg_quote(self::OPEN_TAG) . '){1,2}(.+?)(\\\\' . preg_quote(self::CLOSE_TAG) . '){1,2}/';
        $namedTemplate = preg_replace($pattern, '(?P<$2>.*?)', $quotedTemplate);
        preg_match($namedTemplate, $result, $params);
        if (!$params) {
            throw new ResultTemplateMismatchException('Result not matches original template.');
        }

        $params = array_filter($params, function ($k) { return !is_int($k); }, ARRAY_FILTER_USE_KEY);
        $params = self::parseHtmlParams($params, $template);

        return new self($result, $template, $params);
    }

    /**
     * Валидация шаблона.
     *
     * @param string $template
     * @throws InvalidTemplateException
     */
    private static function validate(string $template)
    {
        $openTagCount = 0;
        $closeTagCount = 0;
        for ($t = 0; $t < mb_strlen($template); $t++) {
            switch ($template[$t]) {
                case self::OPEN_TAG:
                    self::preValidateOpenTag($openTagCount);
                    $openTagCount++;
                    break;
                case self::CLOSE_TAG:
                    self::preValidateCloseTag($openTagCount, $closeTagCount);
                    $closeTagCount++;
                    break;
                default:
                    self::preValidateNoneTag($openTagCount, $closeTagCount, $template[$t]);
                    if ($openTagCount && $openTagCount == $closeTagCount) {
                        $openTagCount = 0;
                        $closeTagCount = 0;
                    }
            }
        }
        if ($openTagCount) {
            // отсутствует закрывающий тег
            throw new InvalidTemplateException('Invalid template.');
        }
    }

    /**
     * Вадидация при обработке открывающих тегов.
     *
     * @param int $openTagCount
     * @throws InvalidTemplateException
     */
    private static function preValidateOpenTag(int $openTagCount)
    {
        if ($openTagCount == 2) {
            throw new InvalidTemplateException('Invalid template.');
        }
    }

    /**
     * Вадидация при обработке закрывающих тегов.
     *
     * @param int $openTagCount
     * @param int $closeTagCount
     * @throws InvalidTemplateException
     */
    private static function preValidateCloseTag(int $openTagCount, int $closeTagCount)
    {
        if (!$openTagCount) {
            // встретился закрывающий тег, хотя не было открывающего
            throw new InvalidTemplateException('Invalid template.');
        }
        if ($closeTagCount == $openTagCount) {
            // лишний закрывающий тег
            throw new InvalidTemplateException('Invalid template.');
        }
    }

    /**
     * Вадидация при обработке всех символов кроме открывающих и закрывающих тегов.
     * Выполняется проверка названия переменных и соответствия открывающих и закрывающих тегов.
     *
     * @param int $openTagCount
     * @param int $closeTagCount
     * @param string $symbol
     * @throws InvalidTemplateException
     */
    private static function preValidateNoneTag(int $openTagCount, int $closeTagCount, string $symbol)
    {
        if ($openTagCount) {
            if (!$closeTagCount) {
                // валидация на спецсимволы названия переменной шаблона
                if (!mb_ereg('[a-z][a-z0-9_]*', $symbol)) {
                    throw new InvalidTemplateException('Invalid template.');
                }
            } elseif ($openTagCount != $closeTagCount) {
                // количество открывающих и закрывающих тегов не совпало
                throw new InvalidTemplateException('Invalid template.');
            }
        }
    }

    /**
     * @param array $params
     * @param string $template
     * @return array
     */
    private static function parseHtmlParams(array $params, string $template): array
    {
        $pattern = '/\\' . self::OPEN_TAG . '{2}.+?\\' . self::CLOSE_TAG . '{2}/';
        preg_match_all($pattern, $template, $htmlParams);
        if ($htmlParams) {
            foreach ($htmlParams[0] as $htmlParam) {
                $key = trim($htmlParam, self::OPEN_TAG . self::CLOSE_TAG);
                $params[$key] = htmlspecialchars_decode($params[$key]);
            }
        }
        return $params;
    }

    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->result;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}