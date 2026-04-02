' =============================================
' Modelo: User.vb
' Modelo principal de usuario con todos los campos
' del perfil incluyendo foto
' =============================================
Namespace CRM_VBNet.Models

    Public Class User
        Public Property Id As Integer
        Public Property Username As String
        Public Property Email As String
        Public Property PasswordHash As String
        Public Property Salt As String
        Public Property FirstName As String
        Public Property LastName As String
        Public Property Phone As String
        Public Property WhatsApp As String
        Public Property Address As String
        Public Property PhotoPath As String
        Public Property RoleId As Integer
        Public Property IsActive As Boolean
        Public Property LastLogin As DateTime?
        Public Property CreatedAt As DateTime
        Public Property UpdatedAt As DateTime

        ' Navegación
        Public Property Role As Role

        ' Propiedad calculada
        Public ReadOnly Property FullName As String
            Get
                Return $"{FirstName} {LastName}"
            End Get
        End Property

        ' Retorna la ruta de foto o un avatar por defecto
        Public ReadOnly Property PhotoUrl As String
            Get
                If String.IsNullOrEmpty(PhotoPath) Then
                    Return "/Content/images/default-avatar.png"
                End If
                Return PhotoPath
            End Get
        End Property

        Public Sub New()
            IsActive = True
            CreatedAt = DateTime.Now
            UpdatedAt = DateTime.Now
        End Sub
    End Class

    ' ViewModel para el login
    Public Class LoginViewModel
        Public Property Username As String
        Public Property Password As String
        Public Property RememberMe As Boolean
    End Class

    ' ViewModel para editar perfil
    Public Class ProfileViewModel
        Public Property Id As Integer
        Public Property FirstName As String
        Public Property LastName As String
        Public Property Email As String
        Public Property Phone As String
        Public Property WhatsApp As String
        Public Property Address As String
        Public Property PhotoPath As String
    End Class

    ' ViewModel para administrar usuarios
    Public Class UserViewModel
        Public Property Id As Integer
        Public Property Username As String
        Public Property Email As String
        Public Property FirstName As String
        Public Property LastName As String
        Public Property Phone As String
        Public Property RoleId As Integer
        Public Property RoleName As String
        Public Property IsActive As Boolean
        Public Property Password As String ' Solo para creación
    End Class

End Namespace
