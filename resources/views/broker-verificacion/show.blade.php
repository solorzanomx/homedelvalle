<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Verifica tus datos — Home del Valle</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;background:#F1F4F8;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;}
.card{background:#fff;border-radius:20px;border:1px solid #E6EAF1;max-width:520px;width:100%;padding:44px 40px;box-shadow:0 4px 24px rgba(0,0,0,.06);}
.logo{margin:0 auto 28px;display:block;height:34px;}
h1{font-size:21px;font-weight:800;color:#0E304B;margin-bottom:10px;letter-spacing:-.3px;text-align:center;}
.subtext{font-size:14.5px;color:#5A6573;line-height:1.65;margin-bottom:26px;text-align:center;}
.form-group{margin-bottom:16px;}
label{display:block;font-size:12.5px;font-weight:700;color:#0E304B;margin-bottom:5px;}
input[type=text], input[type=tel], input[type=url], select{width:100%;border:1.5px solid #D5DCE7;border-radius:10px;padding:11px 13px;font-family:inherit;font-size:14px;color:#0E304B;outline:none;background:#fff;}
input:focus, select:focus{border-color:#2E80C6;}
.privacy-note{font-size:11.5px;color:#94a3b8;line-height:1.6;margin:4px 0 16px;text-align:center;}
.privacy-note a{color:#2270B0;font-weight:700;}
.hint{font-size:11.5px;color:#94a3b8;margin-top:4px;}
.company-toggle{display:flex;gap:10px;margin-bottom:16px;}
.company-opt{flex:1;border:1.5px solid #D5DCE7;border-radius:10px;padding:10px;text-align:center;cursor:pointer;font-size:13px;font-weight:700;color:#5A6573;}
.company-opt.active{border-color:#2E80C6;color:#0E304B;background:#EAF3FB;}
.company-opt input{display:none;}
#companyNameField{display:none;}
.btn{display:block;width:100%;background:#0E304B;color:#fff;border:none;border-radius:12px;padding:15px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;margin-top:8px;}
.btn:hover{opacity:.9;}
.divider{height:1px;background:#EEF1F6;margin:26px 0;}
.footer{font-size:12px;color:#9AA6B5;line-height:1.8;text-align:center;}
.footer strong{color:#0E304B;}
</style>
</head>
<body>
<div class="card">
    <img src="{{ url('img/email/logo-azul.png') }}" alt="Home del Valle" class="logo">

    <h1>¡Nos encanta trabajar con colegas!</h1>
    <p class="subtext">Para poder empezar a compartirte propiedades y comisiones, primero nos gustaría conocerte un poco — nos ayuda a que la colaboración funcione bien para los dos. Tómate un minuto para confirmar tus datos.</p>

    <form method="POST" action="{{ route('broker-verification.submit', $token) }}">
        @csrf

        <div class="form-group">
            <label>Tu nombre</label>
            <input type="text" name="name" value="{{ old('name', $lead->full_name) }}" required>
        </div>

        <div class="form-group">
            <label>¿Trabajas para una empresa o de forma independiente?</label>
            <div class="company-toggle">
                <label class="company-opt" id="optSi" onclick="setCompany(true)">
                    <input type="radio" name="has_company" value="1" {{ old('has_company') === '1' ? 'checked' : '' }}>
                    Empresa
                </label>
                <label class="company-opt active" id="optNo" onclick="setCompany(false)">
                    <input type="radio" name="has_company" value="0" {{ old('has_company', '0') === '0' ? 'checked' : '' }}>
                    Independiente
                </label>
            </div>
        </div>

        <div class="form-group" id="companyNameField">
            <label>Nombre de tu empresa</label>
            <input type="text" name="company_name" value="{{ old('company_name') }}">
        </div>

        <div class="form-group">
            <label>¿En qué zonas trabajas?</label>
            <input type="text" name="interest_zones" value="{{ old('interest_zones') }}" placeholder="Ej. Del Valle, Narvarte, Benito Juárez en general">
            <p class="hint">Separadas por coma, o solo escribe la zona donde te mueves más.</p>
        </div>

        <div class="form-group">
            <label>Teléfono de contacto</label>
            <input type="tel" name="phone" value="{{ old('phone', $lead->phone) }}">
        </div>

        <div class="form-group">
            <label>Número de cédula/licencia (si tienes)</label>
            <input type="text" name="license_number" value="{{ old('license_number') }}">
        </div>

        <div class="form-group">
            <label>Página web (tuya o de tu empresa, si tienes)</label>
            <input type="url" name="website" value="{{ old('website') }}" placeholder="https://...">
        </div>

        <div class="form-group">
            <label>¿Cuántas operaciones cierras al año, aproximadamente?</label>
            <select name="operations_per_year">
                <option value="" {{ old('operations_per_year') === '' ? 'selected' : '' }}>Prefiero no decir</option>
                <option value="1-5" {{ old('operations_per_year') === '1-5' ? 'selected' : '' }}>1 a 5</option>
                <option value="6-15" {{ old('operations_per_year') === '6-15' ? 'selected' : '' }}>6 a 15</option>
                <option value="15+" {{ old('operations_per_year') === '15+' ? 'selected' : '' }}>Más de 15</option>
            </select>
        </div>

        @error('name')<p style="color:#ef4444;font-size:12.5px;margin-top:-8px;margin-bottom:12px;">{{ $message }}</p>@enderror

        <button type="submit" class="btn">Enviar mis datos</button>
        <p class="privacy-note">Al enviar tus datos, aceptas nuestro <a href="{{ route('legal.public', 'aviso-de-privacidad') }}" target="_blank">Aviso de Privacidad</a>.</p>
    </form>

    <div class="divider"></div>

    <div class="footer">
        <strong>Home del Valle</strong><br>
        +52 55 1345 0978 &middot; contacto@homedelvalle.mx
    </div>
</div>

<script>
function setCompany(hasCompany) {
    document.getElementById('optSi').classList.toggle('active', hasCompany);
    document.getElementById('optNo').classList.toggle('active', !hasCompany);
    document.getElementById('companyNameField').style.display = hasCompany ? 'block' : 'none';
}
setCompany(document.querySelector('input[name="has_company"]:checked')?.value === '1');
</script>
</body>
</html>
