' =============================================
' Modelo: Role.vb
' Representa los roles del sistema con permisos granulares
' =============================================
Namespace CRM_VBNet.Models

    Public Class Role
        Public Property Id As Integer
        Public Property Name As String
        Public Property Description As String
        Public Property CanRead As Boolean
        Public Property CanWrite As Boolean
        Public Property CanDelete As Boolean
        Public Property CreatedAt As DateTime

        ' Navegación
        Public Property Users As List(Of User)

        Public Sub New()
            Users = New List(Of User)()
            CanRead = True
            CanWrite = False
            CanDelete = False
            CreatedAt = DateTime.Now
        End Sub
    End Class

End Namespace
