' =============================================
' Modelo: SiteSetting.vb
' Configuración dinámica del sitio (nombre, logo, etc.)
' =============================================
Namespace CRM_VBNet.Models

    Public Class SiteSetting
        Public Property Id As Integer
        Public Property SettingKey As String
        Public Property SettingValue As String
        Public Property UpdatedAt As DateTime
        Public Property UpdatedBy As Integer?

        Public Sub New()
            UpdatedAt = DateTime.Now
        End Sub
    End Class

    ' ViewModel para el formulario de configuración
    Public Class SiteSettingsViewModel
        Public Property SiteName As String
        Public Property LogoPath As String
        Public Property PrimaryColor As String
        Public Property CompanyEmail As String
        Public Property CompanyPhone As String
    End Class

End Namespace
