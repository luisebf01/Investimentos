# Variáveis do Tema Azul Luxo Elegante

Este documento explica as variáveis CSS customizadas (CSS Custom Properties) utilizadas no sistema de carteira de investimentos.

## Cores Principais do Tema

### Cores Primárias (Azul Luxo)
- `--primary-light: #42A5F5` - Azul claro principal
- `--primary-medium: #1976D2` - Azul médio
- `--primary-dark: #0D47A1` - Azul escuro

### Cores de Fundo
- `--bg-primary: #0c0c0c` - Preto principal
- `--bg-secondary: #1a1a1a` - Preto secundário
- `--bg-tertiary: #2d2d2d` - Cinza escuro
- `--bg-card: rgba(30, 30, 30, 0.9)` - Fundo dos cards
- `--bg-input: rgba(40, 40, 40, 0.8)` - Fundo dos inputs

### Cores de Texto
- `--text-primary: #ffffff` - Texto principal (branco)
- `--text-secondary: #ccc` - Texto secundário (cinza claro)
- `--text-muted: #999` - Texto esmaecido (cinza)

### Cores de Interação
- `--primary-shadow: rgba(66, 165, 245, 0.4)` - Sombra azul
- `--primary-hover: rgba(66, 165, 245, 0.05)` - Fundo hover azul
- `--primary-focus: rgba(66, 165, 245, 0.1)` - Foco azul

### Bordas
- `--border-color: #333` - Cor de borda padrão
- `--border-light: #444` - Cor de borda clara

### Cores de Status
- `--success-color: #00C851` - Verde para sucesso
- `--danger-color: #FF4444` - Vermelho para erro/perigo
- `--warning-color: #FFA500` - Laranja para aviso
- `--info-color: #2196F3` - Azul para informação

## Como Usar

### No CSS
```css
.meu-elemento {
    background: var(--primary-light);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}
```

### Classes Utilitárias Disponíveis
```css
.text-primary          /* var(--text-primary) */
.text-secondary        /* var(--text-secondary) */
.text-muted           /* var(--text-muted) */
.text-primary-theme   /* var(--primary-light) */
.text-success         /* var(--success-color) */
.text-danger          /* var(--danger-color) */
.text-info            /* var(--info-color) */

.bg-primary           /* var(--bg-primary) */
.bg-secondary         /* var(--bg-secondary) */
.bg-input             /* var(--bg-input) */

.border-primary       /* var(--border-color) */
.border-light         /* var(--border-light) */
```

## Mudança de Tema

Para alterar o tema para outra cor, simplesmente modifique as variáveis no início do arquivo `style.css`:

```css
:root {
    /* Para tema verde, por exemplo: */
    --primary-light: #66BB6A;
    --primary-medium: #4CAF50;
    --primary-dark: #2E7D32;
    /* Atualizar também as cores de interação */
    --primary-shadow: rgba(102, 187, 106, 0.4);
    --primary-hover: rgba(102, 187, 106, 0.05);
    --primary-focus: rgba(102, 187, 106, 0.1);
}
```

## Gradientes Padrão

O tema utiliza gradientes consistentes:
- **Botões primários**: `linear-gradient(45deg, var(--primary-light), var(--primary-medium))`
- **Hover**: `linear-gradient(45deg, var(--primary-medium), var(--primary-dark))`
- **Títulos**: `linear-gradient(45deg, var(--primary-light), var(--primary-medium))`

## Observações Especiais

1. **SVG Codificado**: No `.form-select`, a cor está codificada na URL do SVG como `%2342A5F5`. Se mudar o tema, esta cor deve ser atualizada manualmente.

2. **Estilos Inline**: Alguns arquivos PHP ainda contêm estilos inline com cores hardcoded. Recomenda-se usar as classes utilitárias sempre que possível.

3. **Responsividade**: Todas as variáveis são responsivas e funcionam em todos os breakpoints.

## Paleta de Cores Atual (Azul Luxo)

### Azuis Principais
- **Claro**: #42A5F5 (RGB: 66, 165, 245)
- **Médio**: #1976D2 (RGB: 25, 118, 210)
- **Escuro**: #0D47A1 (RGB: 13, 71, 161)

### Gradações de Transparência
- **Shadow**: rgba(66, 165, 245, 0.4) - 40% de opacidade
- **Hover**: rgba(66, 165, 245, 0.05) - 5% de opacidade
- **Focus**: rgba(66, 165, 245, 0.1) - 10% de opacidade 